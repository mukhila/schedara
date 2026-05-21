<?php

namespace App\Listeners\Social;

use App\Events\Social\AccountSyncCompleted;
use App\Events\Social\SocialAccountConnected;
use App\Events\Social\SocialAccountDisconnected;
use App\Events\Social\TokenExpired;
use App\Models\SocialLog;
use Illuminate\Support\Facades\Log;

class LogSocialActivity
{
    public function handleConnected(SocialAccountConnected $event): void
    {
        Log::info('Social account connected', [
            'account_id' => $event->account->id,
            'platform'   => $event->account->platform?->slug,
            'tenant_id'  => $event->account->tenant_id,
        ]);
    }

    public function handleDisconnected(SocialAccountDisconnected $event): void
    {
        Log::info('Social account disconnected', [
            'account_id' => $event->account->id,
            'platform'   => $event->account->platform?->slug,
        ]);
    }

    public function handleTokenExpired(TokenExpired $event): void
    {
        Log::warning('Social token expired', [
            'account_id'  => $event->account->id,
            'platform'    => $event->account->platform?->slug,
            'account_name'=> $event->account->account_name,
        ]);
    }

    public function handleSyncCompleted(AccountSyncCompleted $event): void
    {
        Log::info('Social account sync completed', [
            'account_id'  => $event->account->id,
            'pages_count' => $event->pagesCount,
        ]);
    }
}
