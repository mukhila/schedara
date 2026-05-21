<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class RazorpayService
{
    private Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    // ── Customer ─────────────────────────────────────────────────

    public function createOrRetrieveCustomer(Tenant $tenant): string
    {
        if ($tenant->razorpay_customer_id) {
            return $tenant->razorpay_customer_id;
        }

        $owner = $tenant->owner();

        $customer = $this->api->customer->create([
            'name'    => $tenant->name,
            'email'   => $owner?->email ?? 'unknown@schedara.com',
            'contact' => '',
            'notes'   => ['tenant_id' => $tenant->id],
        ]);

        $tenant->update(['razorpay_customer_id' => $customer->id]);

        return $customer->id;
    }

    // ── Subscription ─────────────────────────────────────────────

    public function createSubscription(Tenant $tenant, Plan $plan, string $interval): array
    {
        $planId = $plan->razorpayPlanId($interval);

        if (! $planId) {
            throw new \RuntimeException("No Razorpay plan ID configured for plan [{$plan->slug}] interval [{$interval}].");
        }

        $customerId = $this->createOrRetrieveCustomer($tenant);

        $subscription = $this->api->subscription->create([
            'plan_id'         => $planId,
            'customer_id'     => $customerId,
            'quantity'        => 1,
            'total_count'     => $interval === 'yearly' ? 12 : 120,
            'customer_notify' => 1,
            'notes'           => [
                'tenant_id' => $tenant->id,
                'plan_id'   => $plan->id,
                'interval'  => $interval,
            ],
        ]);

        return [
            'subscription_id' => $subscription->id,
            'short_url'       => $subscription->short_url,
        ];
    }

    // ── Cancel ───────────────────────────────────────────────────

    public function cancelSubscription(string $subscriptionId): void
    {
        try {
            $this->api->subscription->cancel($subscriptionId, ['cancel_at_cycle_end' => 0]);
        } catch (\Throwable $e) {
            Log::warning('Razorpay cancel failed', ['id' => $subscriptionId, 'error' => $e->getMessage()]);
        }
    }

    // ── Plans ────────────────────────────────────────────────────

    public function syncPlan(Plan $plan): void
    {
        // Monthly plan (in paise)
        if ($plan->price_monthly > 0 && ! $plan->razorpay_monthly_plan_id) {
            $rzPlan = $this->api->plan->create([
                'period'   => 'monthly',
                'interval' => 1,
                'item'     => [
                    'name'     => "{$plan->name} Monthly",
                    'amount'   => $plan->price_monthly,
                    'unit'     => 'month',
                    'currency' => 'INR',
                ],
                'notes' => ['plan_id' => $plan->id, 'interval' => 'monthly'],
            ]);
            $plan->razorpay_monthly_plan_id = $rzPlan->id;
        }

        // Yearly plan
        if ($plan->price_yearly > 0 && ! $plan->razorpay_yearly_plan_id) {
            $rzPlan = $this->api->plan->create([
                'period'   => 'yearly',
                'interval' => 1,
                'item'     => [
                    'name'     => "{$plan->name} Yearly",
                    'amount'   => $plan->price_yearly,
                    'unit'     => 'year',
                    'currency' => 'INR',
                ],
                'notes' => ['plan_id' => $plan->id, 'interval' => 'yearly'],
            ]);
            $plan->razorpay_yearly_plan_id = $rzPlan->id;
        }

        $plan->save();
    }

    // ── Payment Verification ──────────────────────────────────────

    public function verifyPayment(string $subscriptionId, string $paymentId, string $signature): bool
    {
        $expectedSignature = hash_hmac(
            'sha256',
            $paymentId . '|' . $subscriptionId,
            config('services.razorpay.key_secret')
        );

        return hash_equals($expectedSignature, $signature);
    }

    public function activateSubscription(Tenant $tenant, Plan $plan, string $razorpaySubscriptionId, string $interval): void
    {
        $rzSub = $this->api->subscription->fetch($razorpaySubscriptionId);

        Subscription::updateOrCreate(
            ['provider' => 'razorpay', 'provider_id' => $razorpaySubscriptionId],
            [
                'tenant_id'            => $tenant->id,
                'plan_id'              => $plan->id,
                'interval'             => $interval,
                'status'               => $rzSub->status === 'active' ? 'active' : 'trialing',
                'current_period_start' => $rzSub->current_start ? now()->setTimestamp($rzSub->current_start) : now(),
                'current_period_end'   => $rzSub->current_end ? now()->setTimestamp($rzSub->current_end) : now()->addMonth(),
            ]
        );

        $tenant->update(['plan_id' => $plan->id, 'status' => 'active']);
    }

    // ── Webhooks ─────────────────────────────────────────────────

    public function handleWebhook(string $payload, string $signature): void
    {
        $secret = config('services.razorpay.webhook_secret');

        $expectedSignature = hash('sha256', $payload . $secret);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Invalid Razorpay webhook signature.');
        }

        $event = json_decode($payload, true);

        match ($event['event'] ?? '') {
            'subscription.activated'     => $this->onSubscriptionActivated($event['payload']['subscription']['entity']),
            'subscription.cancelled'     => $this->onSubscriptionCancelled($event['payload']['subscription']['entity']),
            'payment.captured'           => $this->onPaymentCaptured($event['payload']['payment']['entity']),
            default                      => null,
        };
    }

    private function onSubscriptionActivated(array $sub): void
    {
        Subscription::where('provider', 'razorpay')
                    ->where('provider_id', $sub['id'])
                    ->update([
                        'status'               => 'active',
                        'current_period_start' => isset($sub['current_start']) ? now()->setTimestamp($sub['current_start']) : now(),
                        'current_period_end'   => isset($sub['current_end']) ? now()->setTimestamp($sub['current_end']) : now()->addMonth(),
                    ]);
    }

    private function onSubscriptionCancelled(array $sub): void
    {
        Subscription::where('provider', 'razorpay')
                    ->where('provider_id', $sub['id'])
                    ->update(['status' => 'cancelled']);
    }

    private function onPaymentCaptured(array $payment): void
    {
        if (empty($payment['subscription_id'])) {
            return;
        }

        $sub = Subscription::where('provider', 'razorpay')
                           ->where('provider_id', $payment['subscription_id'])
                           ->first();

        Invoice::updateOrCreate(
            ['provider' => 'razorpay', 'provider_invoice_id' => $payment['id']],
            [
                'tenant_id'       => $sub?->tenant_id,
                'subscription_id' => $sub?->id,
                'status'          => 'paid',
                'amount'          => $payment['amount'],
                'currency'        => strtolower($payment['currency'] ?? 'inr'),
                'description'     => $payment['description'] ?? null,
                'paid_at'         => now(),
            ]
        );
    }
}
