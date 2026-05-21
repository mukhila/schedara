<?php

namespace App\Listeners\Notifications;

use App\Events\Notifications\InAppNotificationCreated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandleInAppNotification
{
    public function handle(InAppNotificationCreated $event): void
    {
        $userId = $event->notification->user_id;

        // Bust the cached unread count so the next bell-icon refresh reflects the new notification
        Cache::forget("unread_notifications:{$userId}");

        Log::debug('InApp notification delivered', [
            'notification_id' => $event->notification->id,
            'user_id'         => $userId,
            'type'            => $event->notification->type,
        ]);
    }
}
