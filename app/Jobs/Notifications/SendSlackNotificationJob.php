<?php

namespace App\Jobs\Notifications;

use App\Models\Notification;
use App\Services\Notifications\Channels\SlackChannelProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSlackNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 20;
    public int $backoff = 60;

    public function __construct(
        private readonly int $notificationId,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(SlackChannelProvider $provider): void
    {
        $notification = Notification::find($this->notificationId);

        if (! $notification) {
            return;
        }

        $provider->send($notification);
    }
}
