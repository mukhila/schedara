<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    // ── Customer ─────────────────────────────────────────────────

    public function createOrRetrieveCustomer(Tenant $tenant): string
    {
        if ($tenant->stripe_customer_id) {
            return $tenant->stripe_customer_id;
        }

        $owner = $tenant->owner();

        $customer = $this->stripe->customers->create([
            'email'    => $owner?->email ?? 'unknown@schedara.com',
            'name'     => $tenant->name,
            'metadata' => ['tenant_id' => $tenant->id],
        ]);

        $tenant->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    // ── Checkout ─────────────────────────────────────────────────

    public function createCheckoutSession(Tenant $tenant, Plan $plan, string $interval): string
    {
        $priceId = $plan->stripePriceId($interval);

        if (! $priceId) {
            throw new \RuntimeException("No Stripe price ID configured for plan [{$plan->slug}] interval [{$interval}].");
        }

        $customerId = $this->createOrRetrieveCustomer($tenant);

        $session = $this->stripe->checkout->sessions->create([
            'customer'            => $customerId,
            'mode'                => 'subscription',
            'payment_method_types' => ['card'],
            'line_items'          => [[
                'price'    => $priceId,
                'quantity' => 1,
            ]],
            'subscription_data' => [
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan_id'   => $plan->id,
                    'interval'  => $interval,
                ],
            ],
            'success_url' => route('billing.index') . '?checkout=success',
            'cancel_url'  => route('billing.plans'),
            'metadata'    => [
                'tenant_id' => $tenant->id,
                'plan_id'   => $plan->id,
                'interval'  => $interval,
            ],
        ]);

        return $session->url;
    }

    // ── Billing Portal ───────────────────────────────────────────

    public function createPortalSession(Tenant $tenant): string
    {
        $customerId = $this->createOrRetrieveCustomer($tenant);

        $session = $this->stripe->billingPortal->sessions->create([
            'customer'   => $customerId,
            'return_url' => route('billing.index'),
        ]);

        return $session->url;
    }

    // ── Plans ────────────────────────────────────────────────────

    public function syncPlan(Plan $plan): void
    {
        // Create or update Stripe product
        if ($plan->stripe_product_id) {
            $product = $this->stripe->products->update($plan->stripe_product_id, [
                'name'   => $plan->name,
                'active' => $plan->is_active,
            ]);
        } else {
            $product = $this->stripe->products->create([
                'name'     => $plan->name,
                'metadata' => ['plan_id' => $plan->id, 'slug' => $plan->slug],
            ]);
            $plan->stripe_product_id = $product->id;
        }

        // Monthly price
        if ($plan->price_monthly > 0) {
            if (! $plan->stripe_monthly_price_id) {
                $price = $this->stripe->prices->create([
                    'product'     => $product->id,
                    'unit_amount' => $plan->price_monthly,
                    'currency'    => 'usd',
                    'recurring'   => ['interval' => 'month'],
                    'nickname'    => "{$plan->name} Monthly",
                ]);
                $plan->stripe_monthly_price_id = $price->id;
            }
        }

        // Yearly price
        if ($plan->price_yearly > 0) {
            if (! $plan->stripe_yearly_price_id) {
                $price = $this->stripe->prices->create([
                    'product'     => $product->id,
                    'unit_amount' => $plan->price_yearly,
                    'currency'    => 'usd',
                    'recurring'   => ['interval' => 'year'],
                    'nickname'    => "{$plan->name} Yearly",
                ]);
                $plan->stripe_yearly_price_id = $price->id;
            }
        }

        $plan->save();
    }

    // ── Subscription Management ──────────────────────────────────

    public function cancelSubscription(string $providerSubId, bool $immediately = false): void
    {
        if ($immediately) {
            $this->stripe->subscriptions->cancel($providerSubId);
        } else {
            $this->stripe->subscriptions->update($providerSubId, ['cancel_at_period_end' => true]);
        }
    }

    public function reactivateSubscription(string $providerSubId): void
    {
        $this->stripe->subscriptions->update($providerSubId, ['cancel_at_period_end' => false]);
    }

    public function getSubscription(string $providerSubId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->retrieve($providerSubId);
    }

    // ── Webhooks ─────────────────────────────────────────────────

    public function handleWebhook(string $payload, string $signature): void
    {
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            throw new \RuntimeException('Invalid Stripe webhook signature.');
        }

        match ($event->type) {
            'checkout.session.completed'         => $this->onCheckoutCompleted($event->data->object),
            'customer.subscription.updated'      => $this->onSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted'      => $this->onSubscriptionDeleted($event->data->object),
            'invoice.payment_succeeded'          => $this->onInvoicePaid($event->data->object),
            'invoice.payment_failed'             => $this->onInvoiceFailed($event->data->object),
            default                              => null,
        };
    }

    private function onCheckoutCompleted(\Stripe\Checkout\Session $session): void
    {
        $tenantId = $session->metadata->tenant_id ?? null;
        $planId   = $session->metadata->plan_id ?? null;
        $interval = $session->metadata->interval ?? 'monthly';

        if (! $tenantId || ! $planId) {
            return;
        }

        $stripeSub = $this->stripe->subscriptions->retrieve($session->subscription);

        Subscription::updateOrCreate(
            ['provider' => 'stripe', 'provider_id' => $stripeSub->id],
            [
                'tenant_id'            => $tenantId,
                'plan_id'              => $planId,
                'interval'             => $interval,
                'status'               => $stripeSub->status,
                'current_period_start' => now()->setTimestamp($stripeSub->current_period_start),
                'current_period_end'   => now()->setTimestamp($stripeSub->current_period_end),
            ]
        );

        Tenant::find($tenantId)?->update(['plan_id' => $planId, 'status' => 'active']);
    }

    private function onSubscriptionUpdated(\Stripe\Subscription $sub): void
    {
        $record = Subscription::where('provider', 'stripe')->where('provider_id', $sub->id)->first();

        if (! $record) {
            return;
        }

        $record->update([
            'status'               => $sub->status,
            'current_period_start' => now()->setTimestamp($sub->current_period_start),
            'current_period_end'   => now()->setTimestamp($sub->current_period_end),
            'cancel_at'            => $sub->cancel_at ? now()->setTimestamp($sub->cancel_at) : null,
        ]);
    }

    private function onSubscriptionDeleted(\Stripe\Subscription $sub): void
    {
        Subscription::where('provider', 'stripe')
                    ->where('provider_id', $sub->id)
                    ->update(['status' => 'cancelled']);
    }

    private function onInvoicePaid(\Stripe\Invoice $stripeInvoice): void
    {
        $sub = Subscription::where('provider', 'stripe')
                           ->where('provider_id', $stripeInvoice->subscription)
                           ->first();

        Invoice::updateOrCreate(
            ['provider' => 'stripe', 'provider_invoice_id' => $stripeInvoice->id],
            [
                'tenant_id'          => $sub?->tenant_id ?? $this->tenantFromCustomer($stripeInvoice->customer),
                'subscription_id'    => $sub?->id,
                'status'             => 'paid',
                'amount'             => $stripeInvoice->amount_paid,
                'currency'           => $stripeInvoice->currency,
                'description'        => $stripeInvoice->description,
                'hosted_invoice_url' => $stripeInvoice->hosted_invoice_url,
                'invoice_pdf_url'    => $stripeInvoice->invoice_pdf,
                'paid_at'            => now()->setTimestamp($stripeInvoice->status_transitions->paid_at ?? time()),
                'period_start'       => $stripeInvoice->period_start ? now()->setTimestamp($stripeInvoice->period_start) : null,
                'period_end'         => $stripeInvoice->period_end ? now()->setTimestamp($stripeInvoice->period_end) : null,
            ]
        );
    }

    private function onInvoiceFailed(\Stripe\Invoice $stripeInvoice): void
    {
        $sub = Subscription::where('provider', 'stripe')
                           ->where('provider_id', $stripeInvoice->subscription)
                           ->first();

        if ($sub) {
            $sub->update(['status' => 'past_due']);
        }

        Log::warning('Stripe invoice payment failed', ['invoice_id' => $stripeInvoice->id]);
    }

    private function tenantFromCustomer(string $customerId): ?int
    {
        return \App\Models\Tenant::where('stripe_customer_id', $customerId)->value('id');
    }
}
