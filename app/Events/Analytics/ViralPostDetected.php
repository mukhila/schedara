<?php

namespace App\Events\Analytics;

use App\Models\PostAnalytic;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViralPostDetected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PostAnalytic $analytic,
        public readonly float        $engagementRate,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('tenant.' . $this->analytic->tenant_id . '.analytics');
    }

    public function broadcastAs(): string
    {
        return 'post.viral';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'         => $this->analytic->post_id,
            'platform'        => $this->analytic->platform,
            'engagement_rate' => $this->engagementRate,
            'threshold'       => config('analytics.viral_threshold', 5.0),
        ];
    }
}
