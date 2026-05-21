<?php

namespace App\Jobs\Social;

use App\Models\SocialAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class CheckExpiredAccountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        // Find all accounts whose token expires within the next hour
        SocialAccount::with('platform')
            ->needsRefresh()
            ->chunk(50, function ($accounts) {
                foreach ($accounts as $account) {
                    RefreshSocialTokenJob::dispatch($account)
                        ->onQueue('social')
                        ->delay(now()->addSeconds(rand(0, 30))); // stagger requests
                }
            });
    }
}
