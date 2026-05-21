<?php

namespace App\Jobs\Social;

use App\Models\SocialAccount;
use App\Services\Social\SocialAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshSocialTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(private readonly SocialAccount $account) {}

    public function handle(SocialAuthService $service): void
    {
        try {
            $service->refreshToken($this->account);
        } catch (\Throwable $e) {
            Log::error('RefreshSocialTokenJob failed', [
                'account_id' => $this->account->id,
                'error'      => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    public function uniqueId(): string
    {
        return 'refresh-token-' . $this->account->id;
    }
}
