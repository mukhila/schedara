<?php

namespace App\Jobs\Billing;

use App\Events\Billing\PaymentFailed;
use App\Models\BillingLog;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Billing\BillingManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryFailedPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1; // We manage retries manually via retry_count
    public int $timeout = 120;

    public function __construct(public readonly int $subscriptionId)
    {
        $this->onQueue('billing');
    }

    public function handle(BillingManager $billing): void
    {
        $subscription = Subscription::with(['tenant', 'plan'])->find($this->subscriptionId);

        if (! $subscription || ! $subscription->isActive() && $subscription->status !== 'past_due') {
            return;
        }

        if ($subscription->status !== 'past_due') {
            return;
        }

        try {
            if ($subscription->isStripe()) {
                // Stripe automatically retries; we just log this attempt
                $stripeSub = $billing->stripe()->getSubscription($subscription->provider_id);

                BillingLog::record(
                    'payment.retry',
                    ['subscription_id' => $subscription->id, 'status' => $stripeSub->status],
                    [],
                    'success',
                    'stripe',
                    $subscription->tenant_id
                );
            }
        } catch (\Throwable $e) {
            Log::error('Retry payment failed', ['subscription_id' => $this->subscriptionId, 'error' => $e->getMessage()]);
            event(new PaymentFailed($subscription, $e->getMessage()));
        }
    }
}
