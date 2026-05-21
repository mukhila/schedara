<?php

namespace App\Events\Collaboration;

use App\Models\Post;
use App\Models\PostApproval;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostApprovalRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PostApproval $approval,
        public readonly Post         $post,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("tenant.{$this->approval->tenant_id}")];
    }

    public function broadcastAs(): string
    {
        return 'approval.requested';
    }

    public function broadcastWith(): array
    {
        return [
            'approval_uuid' => $this->approval->uuid,
            'post_title'    => $this->post->title ?? 'Post',
        ];
    }
}
