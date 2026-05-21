<?php

namespace App\Events\Analytics;

use App\Models\AnalyticsCampaign;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ROIThresholdReached implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly AnalyticsCampaign $campaign,
        public readonly float             $roi,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('tenant.' . $this->campaign->tenant_id . '.analytics');
    }

    public function broadcastAs(): string
    {
        return 'roi.threshold';
    }

    public function broadcastWith(): array
    {
        return [
            'campaign_id' => $this->campaign->uuid,
            'name'        => $this->campaign->name,
            'roi'         => $this->roi,
            'threshold'   => config('analytics.roi_alert_threshold', 100),
        ];
    }
}
