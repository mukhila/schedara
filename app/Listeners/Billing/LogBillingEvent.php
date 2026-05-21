<?php

namespace App\Listeners\Billing;

use App\Events\Billing\CouponApplied;
use App\Events\Billing\PaymentFailed;
use App\Events\Billing\PaymentSuccessful;
use App\Events\Billing\SubscriptionCancelled;
use App\Events\Billing\SubscriptionCreated;
use App\Events\Billing\SubscriptionRenewed;
use App\Events\Billing\TrialExpired;
use App\Models\BillingLog;

class LogBillingEvent
{
    public function handleSubscriptionCreated(SubscriptionCreated $event): void
    {
        BillingLog::record('subscription.created', ['plan_id' => $event->subscription->plan_id], [], 'success', $event->subscription->provider, $event->subscription->tenant_id);
    }

    public function handleSubscriptionRenewed(SubscriptionRenewed $event): void
    {
        BillingLog::record('subscription.renewed', ['plan_id' => $event->subscription->plan_id], [], 'success', $event->subscription->provider, $event->subscription->tenant_id);
    }

    public function handleSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        BillingLog::record('subscription.cancelled', [], [], 'success', $event->subscription->provider, $event->subscription->tenant_id);
    }

    public function handlePaymentSuccessful(PaymentSuccessful $event): void
    {
        BillingLog::record('payment.success', ['amount' => $event->amountCents], [], 'success', $event->subscription->provider, $event->subscription->tenant_id);
    }

    public function handlePaymentFailed(PaymentFailed $event): void
    {
        BillingLog::record('payment.failed', ['reason' => $event->reason], [], 'failed', $event->subscription->provider, $event->subscription->tenant_id);
    }

    public function handleTrialExpired(TrialExpired $event): void
    {
        BillingLog::record('trial.expired', [], [], 'success', $event->subscription->provider, $event->subscription->tenant_id);
    }

    public function handleCouponApplied(CouponApplied $event): void
    {
        BillingLog::record('coupon.applied', ['coupon_code' => $event->coupon->coupon_code], [], 'success', null, $event->subscription->tenant_id);
    }
}
