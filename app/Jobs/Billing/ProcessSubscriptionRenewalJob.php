<?php

namespace App\Jobs\Billing;

use App\Models\Subscription;
use App\Services\Billing\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionRenewalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct()
    {
        $this->onQueue('billing');
    }

    public function handle(SubscriptionService $service): void
    {
        // Expire trials
        $trialExpired = $service->processExpiredTrials();

        // Cancel subscriptions past their cancel_at date
        $cancelled = $service->processExpiredSubscriptions();

        Log::info('Subscription renewal job completed', [
            'trials_expired' => $trialExpired,
            'subscriptions_cancelled' => $cancelled,
        ]);
    }
}
