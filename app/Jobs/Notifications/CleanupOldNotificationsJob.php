<?php

namespace App\Jobs\Notifications;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupOldNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 120;

    public function __construct(public readonly int $daysOlderThan = 60) {}

    public function handle(): void
    {
        Notification::where('created_at', '<', now()->subDays($this->daysOlderThan))->delete();
    }
}
