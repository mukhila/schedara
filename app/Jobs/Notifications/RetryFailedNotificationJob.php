<?php

namespace App\Jobs\Notifications;

use App\Models\NotificationLog;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RetryFailedNotificationJob implements ShouldQueue
{
    use Queueable, Batchable;

    public int $tries   = 1;
    public int $timeout = 60;

    public function __construct(
        private readonly int    $notificationLogId,
        private readonly string $channel,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $log = NotificationLog::find($this->notificationLogId);

        if (! $log || ! $log->isFailed()) {
            return;
        }

        if (! $log->notification) {
            Log::info('RetryFailedNotificationJob: notification deleted, skipping', ['log_id' => $this->notificationLogId]);
            return;
        }

        $notification = $log->notification;
        $maxAttempts  = 3;

        if ($log->attempts >= $maxAttempts) {
            Log::warning('RetryFailedNotificationJob: max attempts reached', ['log_id' => $log->id]);
            return;
        }

        $log->increment('attempts');
        $log->update(['delivery_status' => 'pending']);

        try {
            match ($this->channel) {
                'push'      => SendPushNotificationJob::dispatchSync($notification->user_id, $notification->id),
                'whatsapp'  => SendWhatsAppNotificationJob::dispatchSync($notification->user_id, $notification->id),
                'sms'       => SendSmsNotificationJob::dispatchSync($notification->user_id, $notification->id),
                'slack'     => SendSlackNotificationJob::dispatchSync($notification->id),
                default     => null,
            };
        } catch (\Throwable $e) {
            $log->update(['delivery_status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::warning("RetryFailedNotificationJob failed for channel {$this->channel}", ['error' => $e->getMessage()]);
        }
    }
}
