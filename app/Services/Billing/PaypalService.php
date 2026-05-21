<?php

namespace App\Services\Billing;

use App\Models\BillingLog;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaypalService
{
    private string $baseUrl;
    private string $clientId;
    private string $secret;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id', '');
        $this->secret   = config('services.paypal.secret', '');
        $this->baseUrl  = config('services.paypal.sandbox', true)
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    // ── Auth ─────────────────────────────────────────────────────

    private function accessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->secret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            throw new \RuntimeException('PayPal auth failed: ' . $response->body());
        }

        return $response->json('access_token');
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken($this->accessToken())
                   ->withHeaders(['Content-Type' => 'application/json']);
    }

    // ── Subscription ─────────────────────────────────────────────

    public function createSubscription(Tenant $tenant, Plan $plan, string $interval): array
    {
        $planId = $plan->paypalPlanId($interval);

        if (! $planId) {
            throw new \RuntimeException("No PayPal plan ID configured for [{$plan->slug}] [{$interval}].");
        }

        $response = $this->http()->post("{$this->baseUrl}/v1/billing/subscriptions", [
            'plan_id'           => $planId,
            'subscriber'        => [
                'name'          => ['given_name' => $tenant->name],
                'email_address' => $tenant->owner()?->email ?? 'unknown@schedara.com',
            ],
            'application_context' => [
                'brand_name'          => config('app.name'),
                'return_url'          => route('billing.paypal.callback') . "?plan_id={$plan->id}&interval={$interval}",
                'cancel_url'          => route('billing.plans'),
                'user_action'         => 'SUBSCRIBE_NOW',
                'payment_method'      => ['payer_selected' => 'PAYPAL', 'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'],
            ],
            'custom_id' => "tenant:{$tenant->id}:plan:{$plan->id}:interval:{$interval}",
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('PayPal subscription creation failed: ' . $response->body());
        }

        $data     = $response->json();
        $approveUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'] ?? null;

        if (! $approveUrl) {
            throw new \RuntimeException('PayPal did not return an approval URL.');
        }

        BillingLog::record('paypal.subscription.create', ['plan_id' => $plan->id], $data, 'success', 'paypal', $tenant->id);

        return [
            'subscription_id' => $data['id'],
            'approve_url'     => $approveUrl,
        ];
    }

    public function activateSubscription(Tenant $tenant, Plan $plan, string $paypalSubscriptionId, string $interval): Subscription
    {
        $response = $this->http()->get("{$this->baseUrl}/v1/billing/subscriptions/{$paypalSubscriptionId}");

        if ($response->failed()) {
            throw new \RuntimeException('Could not retrieve PayPal subscription.');
        }

        $data = $response->json();

        $sub = Subscription::updateOrCreate(
            ['provider' => 'paypal', 'provider_id' => $paypalSubscriptionId],
            [
                'tenant_id'             => $tenant->id,
                'plan_id'               => $plan->id,
                'interval'              => $interval,
                'status'                => $data['status'] === 'ACTIVE' ? 'active' : 'trialing',
                'current_period_start'  => now(),
                'current_period_end'    => $interval === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]
        );

        $tenant->update(['plan_id' => $plan->id, 'status' => 'active']);

        return $sub;
    }

    public function cancelSubscription(string $subscriptionId, string $reason = 'User requested cancellation'): void
    {
        $this->http()->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/cancel", [
            'reason' => $reason,
        ]);
    }

    // ── Webhook Verification ──────────────────────────────────────

    public function verifyWebhookSignature(array $headers, string $body): bool
    {
        // PayPal recommends calling the verify-webhook-signature API.
        $response = $this->http()->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
            'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? '',
            'cert_url'          => $headers['PAYPAL-CERT-URL'] ?? '',
            'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
            'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
            'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
            'webhook_id'        => config('services.paypal.webhook_id', ''),
            'webhook_event'     => json_decode($body, true),
        ]);

        return $response->json('verification_status') === 'SUCCESS';
    }

    // ── Webhook Event Handling ────────────────────────────────────

    public function handleWebhook(string $payload, array $headers): void
    {
        if (! $this->verifyWebhookSignature($headers, $payload)) {
            throw new \RuntimeException('Invalid PayPal webhook signature.');
        }

        $event = json_decode($payload, true);

        match ($event['event_type'] ?? '') {
            'BILLING.SUBSCRIPTION.ACTIVATED'  => $this->onSubscriptionActivated($event),
            'BILLING.SUBSCRIPTION.CANCELLED'  => $this->onSubscriptionCancelled($event),
            'PAYMENT.SALE.COMPLETED'          => $this->onPaymentCompleted($event),
            'PAYMENT.SALE.DENIED'             => $this->onPaymentDenied($event),
            default                           => null,
        };
    }

    private function onSubscriptionActivated(array $event): void
    {
        $subId = $event['resource']['id'] ?? null;
        if (! $subId) {
            return;
        }

        Subscription::where('provider', 'paypal')->where('provider_id', $subId)->update(['status' => 'active']);
    }

    private function onSubscriptionCancelled(array $event): void
    {
        $subId = $event['resource']['id'] ?? null;
        if (! $subId) {
            return;
        }

        Subscription::where('provider', 'paypal')->where('provider_id', $subId)->update(['status' => 'cancelled']);
    }

    private function onPaymentCompleted(array $event): void
    {
        $resource = $event['resource'] ?? [];
        $billingAgreementId = $resource['billing_agreement_id'] ?? null;

        if (! $billingAgreementId) {
            return;
        }

        $sub = Subscription::where('provider', 'paypal')->where('provider_id', $billingAgreementId)->first();

        Invoice::updateOrCreate(
            ['provider' => 'paypal', 'provider_invoice_id' => $resource['id']],
            [
                'tenant_id'       => $sub?->tenant_id,
                'subscription_id' => $sub?->id,
                'status'          => 'paid',
                'amount'          => (int) (($resource['amount']['total'] ?? 0) * 100),
                'currency'        => strtolower($resource['amount']['currency'] ?? 'usd'),
                'paid_at'         => now(),
            ]
        );
    }

    private function onPaymentDenied(array $event): void
    {
        $resource    = $event['resource'] ?? [];
        $subId       = $resource['billing_agreement_id'] ?? null;

        if ($subId) {
            Subscription::where('provider', 'paypal')->where('provider_id', $subId)->update(['status' => 'past_due']);
        }

        Log::warning('PayPal payment denied', ['resource_id' => $resource['id'] ?? null]);
    }
}
