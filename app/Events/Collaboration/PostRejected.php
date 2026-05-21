<?php

namespace App\Events\Collaboration;

use App\Models\PostApproval;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly PostApproval $approval) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("tenant.{$this->approval->tenant_id}")];
    }

    public function broadcastAs(): string { return 'post.rejected'; }

    public function broadcastWith(): array
    {
        return [
            'approval_uuid' => $this->approval->uuid,
            'reason'        => $this->approval->reviewer_comment,
        ];
    }
}
