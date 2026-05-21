<?php

namespace App\Services\Notifications;

use App\Jobs\Notifications\SendPushNotificationJob;
use App\Jobs\Notifications\SendSlackNotificationJob;
use App\Jobs\Notifications\SendSmsNotificationJob;
use App\Jobs\Notifications\SendWhatsAppNotificationJob;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;

class NotificationChannelRouter
{
    /**
     * Dispatch outbound channel jobs for a single user based on their preferences.
     * In-app (database) and broadcast are handled by NotificationService::send() directly.
     */
    public function dispatchForUser(User $user, Notification $notification): void
    {
        $category = $notification->category;

        if (NotificationPreference::isEnabled($user->id, $category, 'push')) {
            SendPushNotificationJob::dispatch($user->id, $notification->id)
                ->onQueue('notifications');
        }

        if (NotificationPreference::isEnabled($user->id, $category, 'whatsapp')) {
            SendWhatsAppNotificationJob::dispatch($user->id, $notification->id)
                ->onQueue('notifications');
        }

        if (NotificationPreference::isEnabled($user->id, $category, 'sms')) {
            SendSmsNotificationJob::dispatch($user->id, $notification->id)
                ->onQueue('notifications');
        }

        // Slack is tenant-level; dispatch once per notification (deduplicated inside job)
        if ($notification->tenant_id && NotificationPreference::isEnabled($user->id, $category, 'slack')) {
            SendSlackNotificationJob::dispatch($notification->id)
                ->onQueue('notifications');
        }
    }
}
