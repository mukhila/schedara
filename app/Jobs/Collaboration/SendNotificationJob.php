<?php

namespace App\Jobs\Collaboration;

use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        private readonly int     $userId,
        private readonly string  $type,
        private readonly string  $category,
        private readonly string  $title,
        private readonly string  $body,
        private readonly array   $payload    = [],
        private readonly ?string $actionUrl  = null,
        private readonly string  $priority   = 'normal',
        private readonly ?int    $tenantId   = null,
    ) {
        $this->onQueue('collaboration');
    }

    public function handle(NotificationService $service): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            return;
        }

        $service->send(
            user:       $user,
            type:       $this->type,
            category:   $this->category,
            title:      $this->title,
            body:       $this->body,
            payload:    $this->payload,
            actionUrl:  $this->actionUrl,
            priority:   $this->priority,
            tenantId:   $this->tenantId,
        );
    }
}
