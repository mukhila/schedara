<?php

namespace App\Events\Analytics;

use App\Models\AnalyticsCampaign;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly AnalyticsCampaign $campaign) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('tenant.' . $this->campaign->tenant_id . '.analytics');
    }

    public function broadcastAs(): string
    {
        return 'campaign.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'campaign_id' => $this->campaign->uuid,
            'name'        => $this->campaign->name,
            'roi'         => $this->campaign->roi,
            'roas'        => $this->campaign->roas,
        ];
    }
}
