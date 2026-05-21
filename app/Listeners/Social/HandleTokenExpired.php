<?php

namespace App\Listeners\Social;

use App\Events\Social\TokenExpired;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleTokenExpired implements ShouldQueue
{
    public function handle(TokenExpired $event): void
    {
        $account = $event->account;

        // In-app notification to the workspace owner
        $account->tenant?->users()
            ->whereHas('memberships', fn ($q) => $q->whereIn('role', ['owner', 'admin']))
            ->get()
            ->each(function ($user) use ($account) {
                // Dispatch a database notification
                $user->notify(new \App\Notifications\Social\TokenExpiredNotification($account));
            });
    }
}
