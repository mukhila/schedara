<?php

namespace App\Jobs\Social;

use App\Events\Social\AccountSyncCompleted;
use App\Models\SocialAccount;
use App\Services\Social\SocialAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSocialAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(private readonly SocialAccount $account) {}

    public function handle(SocialAuthService $service): void
    {
        if (! $this->account->isActive()) {
            return;
        }

        try {
            $service->syncProfile($this->account);
            $pages = $service->syncPages($this->account);
            event(new AccountSyncCompleted($this->account, count($pages)));
        } catch (\Throwable $e) {
            Log::error('SyncSocialAccountJob failed', [
                'account_id' => $this->account->id,
                'error'      => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    public function uniqueId(): string
    {
        return 'sync-account-' . $this->account->id;
    }
}
