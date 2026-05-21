<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;

class BillingManager
{
    public function __construct(
        private readonly StripeService    $stripe,
        private readonly RazorpayService  $razorpay,
        private readonly PaypalService    $paypalService,
    ) {}

    /**
     * Return the service for the given provider slug.
     * Defaults based on currency: INR → Razorpay, USD → Stripe.
     */
    public function for(string $provider): StripeService|RazorpayService
    {
        return match ($provider) {
            'razorpay' => $this->razorpay,
            default    => $this->stripe,
        };
    }

    public function stripe(): StripeService
    {
        return $this->stripe;
    }

    public function razorpay(): RazorpayService
    {
        return $this->razorpay;
    }

    public function paypal(): PaypalService
    {
        return $this->paypalService;
    }

    /**
     * Resolve provider from tenant's active subscription,
     * or fall back to config default.
     */
    public function resolveProvider(Tenant $tenant): string
    {
        return $tenant->subscription?->provider ?? config('services.billing.default_provider', 'stripe');
    }

    /**
     * Create a checkout/subscription URL for the given plan + interval.
     * Returns an array with `url` (redirect) and `provider` for Razorpay/PayPal SDK flow.
     */
    public function initiateCheckout(Tenant $tenant, Plan $plan, string $interval, string $provider): array
    {
        if ($provider === 'razorpay') {
            $result = $this->razorpay->createSubscription($tenant, $plan, $interval);

            return [
                'provider'    => 'razorpay',
                'url'         => $result['short_url'],
                'rzp_sub_id'  => $result['subscription_id'],
                'key'         => config('services.razorpay.key_id'),
            ];
        }

        if ($provider === 'paypal') {
            $result = $this->paypalService->createSubscription($tenant, $plan, $interval);

            return [
                'provider'        => 'paypal',
                'url'             => $result['approve_url'],
                'subscription_id' => $result['subscription_id'],
            ];
        }

        return [
            'provider' => 'stripe',
            'url'      => $this->stripe->createCheckoutSession($tenant, $plan, $interval),
        ];
    }

    /**
     * Cancel the active subscription for a tenant.
     */
    public function cancel(Subscription $subscription, bool $immediately = false): void
    {
        $this->for($subscription->provider)
             ->cancelSubscription($subscription->provider_id, $immediately);
    }
}
