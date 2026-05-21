<?php

namespace App\Events\Analytics;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalyticsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $type,
        public readonly array  $summary,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('tenant.' . $this->tenantId . '.analytics');
    }

    public function broadcastAs(): string
    {
        return 'analytics.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'type'    => $this->type,
            'summary' => $this->summary,
        ];
    }
}
