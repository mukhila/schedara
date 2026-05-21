<?php

namespace App\Listeners\Billing;

use App\Events\Billing\PaymentFailed;
use App\Events\Billing\PaymentSuccessful;
use App\Events\Billing\SubscriptionCancelled;
use App\Events\Billing\SubscriptionCreated;
use App\Events\Billing\TrialExpired;
use App\Jobs\Billing\SendBillingReminderJob;
use App\Jobs\Billing\SendInvoiceEmailJob;
use Illuminate\Support\Facades\Log;

class SendBillingNotification
{
    public function handleSubscriptionCreated(SubscriptionCreated $event): void
    {
        SendBillingReminderJob::dispatch($event->subscription->id, 'renewal');
    }

    public function handlePaymentSuccessful(PaymentSuccessful $event): void
    {
        if ($event->invoice) {
            SendInvoiceEmailJob::dispatch($event->invoice->id);
        }
    }

    public function handlePaymentFailed(PaymentFailed $event): void
    {
        SendBillingReminderJob::dispatch($event->subscription->id, 'payment_failed');
    }

    public function handleTrialExpired(TrialExpired $event): void
    {
        SendBillingReminderJob::dispatch($event->subscription->id, 'trial_expiring');
    }

    public function handleCancelled(SubscriptionCancelled $event): void
    {
        Log::info('Subscription cancelled', ['subscription_id' => $event->subscription->id]);
    }
}
