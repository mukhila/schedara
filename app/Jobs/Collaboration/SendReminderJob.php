<?php

namespace App\Jobs\Collaboration;

use App\Models\CollaborationTask;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(
        private readonly int    $taskId,
        private readonly string $reminderType = 'due_soon', // 'due_soon' | 'overdue'
    ) {
        $this->onQueue('collaboration');
    }

    public function handle(NotificationService $notifications): void
    {
        $task = CollaborationTask::find($this->taskId);
        if (!$task || $task->statusEnum()->isTerminal()) {
            return;
        }

        $assignee = User::find($task->assigned_to);
        if (!$assignee) {
            return;
        }

        [$title, $body] = match ($this->reminderType) {
            'due_soon' => [
                'Task due soon',
                "\"{$task->title}\" is due {$task->due_date->diffForHumans()}.",
            ],
            'overdue' => [
                'Task overdue',
                "\"{$task->title}\" was due {$task->due_date->diffForHumans()} and is still open.",
            ],
            default => ['Task reminder', "You have a pending task: \"{$task->title}\"."],
        };

        $notifications->send(
            user:      $assignee,
            type:      "task_{$this->reminderType}",
            category:  'collaboration',
            title:     $title,
            body:      $body,
            payload:   ['task_uuid' => $task->uuid],
            actionUrl: url("/collaboration/tasks/{$task->uuid}"),
            priority:  $this->reminderType === 'overdue' ? 'high' : 'normal',
            tenantId:  $task->tenant_id,
        );
    }
}
