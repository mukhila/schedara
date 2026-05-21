<?php

namespace App\Jobs\Analytics;

use App\Events\Analytics\CampaignCompleted;
use App\Models\AnalyticsCampaign;
use App\Services\Analytics\CampaignAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCampaignAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 180;

    public function __construct(public readonly AnalyticsCampaign $campaign) {}

    public function handle(CampaignAnalyticsService $service): void
    {
        // Stub — pull metrics from platform APIs and persist
        $metrics = $this->fetchCampaignMetrics($this->campaign);

        if (!empty($metrics)) {
            $service->updateMetrics($this->campaign, $metrics);
        }

        // Auto-complete if past end date
        if ($this->campaign->end_date && $this->campaign->end_date->isPast()
            && $this->campaign->status === 'active') {
            $service->markCompleted($this->campaign);
        }
    }

    private function fetchCampaignMetrics(AnalyticsCampaign $campaign): array
    {
        // Stub — integrate platform ad APIs here
        return [];
    }
}
