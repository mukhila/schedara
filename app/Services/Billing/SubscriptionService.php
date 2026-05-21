<?php

namespace App\Services\Billing;

use App\Events\Billing\SubscriptionCancelled;
use App\Events\Billing\SubscriptionCreated;
use App\Events\Billing\TrialExpired;
use App\Models\BillingLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(
        private readonly StripeService    $stripe,
        private readonly RazorpayService  $razorpay,
        private readonly PaypalService    $paypal,
        private readonly UsageLimitService $usageLimit,
    ) {}

    /**
     * Activate a free-plan subscription with optional trial.
     */
    public function activateFree(Tenant $tenant, Plan $plan): Subscription
    {
        $trialDays  = $plan->trial_days ?? 0;
        $trialEnds  = $trialDays > 0 ? now()->addDays($trialDays) : null;

        $sub = Subscription::updateOrCreate(
            ['tenant_id' => $tenant->id, 'provider' => 'free'],
            [
                'plan_id'               => $plan->id,
                'provider_id'           => 'free-' . $tenant->id,
                'interval'              => 'monthly',
                'status'                => $trialDays > 0 ? 'trialing' : 'active',
                'current_period_start'  => now(),
                'current_period_end'    => now()->addMonth(),
                'trial_ends_at'         => $trialEnds,
            ]
        );

        $tenant->update(['plan_id' => $plan->id, 'status' => 'active']);
        $this->usageLimit->syncFromSubscription($sub);

        event(new SubscriptionCreated($sub));

        return $sub;
    }

    /**
     * Cancel a subscription. If $immediately=false, cancels at period end.
     */
    public function cancel(Subscription $subscription, bool $immediately = false): Subscription
    {
        try {
            if ($subscription->isStripe()) {
                $this->stripe->cancelSubscription($subscription->provider_id, $immediately);
            } elseif ($subscription->isRazorpay()) {
                $this->razorpay->cancelSubscription($subscription->provider_id);
            } elseif ($subscription->isPaypal()) {
                $this->paypal->cancelSubscription($subscription->provider_id);
            }
        } catch (\Throwable $e) {
            Log::error('Subscription cancel failed', ['error' => $e->getMessage(), 'sub' => $subscription->id]);
        }

        $updates = $immediately
            ? ['status' => 'cancelled', 'cancel_at' => now()]
            : ['cancel_at' => $subscription->current_period_end];

        $subscription->update($updates);

        BillingLog::record('subscription.cancel', ['subscription_id' => $subscription->id, 'immediately' => $immediately], [], 'success', $subscription->provider, $subscription->tenant_id);
        event(new SubscriptionCancelled($subscription));

        return $subscription->fresh();
    }

    /**
     * Pause a subscription (Stripe only via billing portal / manual).
     */
    public function pause(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status'    => 'paused',
            'paused_at' => now(),
        ]);

        BillingLog::record('subscription.paused', ['subscription_id' => $subscription->id], [], 'success', $subscription->provider, $subscription->tenant_id);

        return $subscription->fresh();
    }

    /**
     * Resume a paused subscription.
     */
    public function resume(Subscription $subscription): Subscription
    {
        if (! $subscription->isPaused()) {
            return $subscription;
        }

        $subscription->update([
            'status'    => 'active',
            'paused_at' => null,
        ]);

        BillingLog::record('subscription.resumed', ['subscription_id' => $subscription->id], [], 'success', $subscription->provider, $subscription->tenant_id);

        return $subscription->fresh();
    }

    /**
     * Switch subscription to a new plan (immediate upgrade / end-of-period downgrade).
     * Returns redirect URL or ['success' => true].
     */
    public function upgrade(Tenant $tenant, Subscription $subscription, Plan $newPlan, string $interval): string
    {
        if ($subscription->isStripe() && $subscription->provider_id) {
            return $this->stripe->createCheckoutSession($tenant, $newPlan, $interval);
        }

        if ($subscription->isRazorpay() && $subscription->provider_id) {
            $result = $this->razorpay->createSubscription($tenant, $newPlan, $interval);
            return $result['short_url'];
        }

        return $this->stripe->createCheckoutSession($tenant, $newPlan, $interval);
    }

    /**
     * Mark expired trials and fire TrialExpired event.
     */
    public function processExpiredTrials(): int
    {
        $expired = Subscription::where('status', 'trialing')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expired as $sub) {
            $sub->update(['status' => 'expired']);
            event(new TrialExpired($sub));

            BillingLog::record('trial.expired', ['subscription_id' => $sub->id], [], 'success', $sub->provider, $sub->tenant_id);
        }

        return $expired->count();
    }

    /**
     * Expire subscriptions past their end date.
     */
    public function processExpiredSubscriptions(): int
    {
        return Subscription::where('status', 'active')
            ->whereNotNull('cancel_at')
            ->where('cancel_at', '<', now())
            ->update(['status' => 'cancelled']);
    }
}
