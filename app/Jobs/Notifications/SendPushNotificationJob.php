<?php

namespace App\Jobs\Notifications;

use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\Channels\PushChannelProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 30;
    public int $backoff = 60;

    public function __construct(
        private readonly int $userId,
        private readonly int $notificationId,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(PushChannelProvider $provider): void
    {
        $user         = User::find($this->userId);
        $notification = Notification::find($this->notificationId);

        if (! $user || ! $notification) {
            return;
        }

        $provider->send($user, $notification);
    }
}
