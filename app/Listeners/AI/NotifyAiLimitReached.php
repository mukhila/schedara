<?php

namespace App\Listeners\AI;

use App\Events\AI\AiLimitReached;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAiLimitReached implements ShouldQueue
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(AiLimitReached $event): void
    {
        $user = User::find($event->userId);
        if (!$user) return;

        $this->notifications->send(
            user:      $user,
            type:      'ai_limit_reached',
            category:  'system',
            title:     'AI monthly limit reached',
            body:      'Your workspace has reached the monthly AI token limit. Upgrade your plan for more AI usage.',
            priority:  'high',
            tenantId:  $event->tenantId,
        );
    }
}
