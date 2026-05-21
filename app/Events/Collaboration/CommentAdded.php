<?php

namespace App\Events\Collaboration;

use App\Models\InternalComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly InternalComment $comment) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("tenant.{$this->comment->tenant_id}")];
    }

    public function broadcastAs(): string { return 'comment.added'; }

    public function broadcastWith(): array
    {
        return [
            'comment_uuid' => $this->comment->uuid,
            'post_id'      => $this->comment->post_id,
            'task_id'      => $this->comment->task_id,
            'author'       => $this->comment->author->name ?? 'Unknown',
        ];
    }
}
