<?php

namespace App\Jobs\Notifications;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\System\DigestNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDigestEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300;

    public function __construct(public readonly string $period = 'weekly') {}

    public function handle(): void
    {
        $since = $this->period === 'daily' ? now()->subDay() : now()->subWeek();

        User::whereHas('notifications', fn ($q) => $q->where('created_at', '>=', $since))
            ->chunk(50, function ($users) use ($since) {
                foreach ($users as $user) {
                    $notifications = Notification::forUser($user->id)
                        ->where('created_at', '>=', $since)
                        ->orderByDesc('created_at')
                        ->get();

                    if ($notifications->isEmpty()) {
                        continue;
                    }

                    $user->notify(new DigestNotification($notifications, $this->period));
                }
            });
    }
}
