<?php

namespace App\Events\Collaboration;

use App\Models\CollaborationTask;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly CollaborationTask $task,
        public readonly User              $assignedBy,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("tenant.{$this->task->tenant_id}")];
    }

    public function broadcastAs(): string
    {
        return 'task.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'task_uuid'    => $this->task->uuid,
            'task_title'   => $this->task->title,
            'assigned_to'  => $this->task->assigned_to,
            'assigned_by'  => $this->assignedBy->name,
        ];
    }
}
