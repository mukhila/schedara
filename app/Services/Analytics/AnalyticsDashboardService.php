<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Repositories\Analytics\AnalyticsCampaignRepository;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use Illuminate\Support\Facades\Cache;

class AnalyticsDashboardService
{
    public function __construct(
        private AnalyticsMetricsRepository  $metricsRepo,
        private AnalyticsCampaignRepository $campaignRepo,
    ) {}

    public function overview(AnalyticsFilterDTO $f): array
    {
        $cacheKey = "analytics:overview:{$f->tenantId}:{$f->range->fromString()}:{$f->range->toString()}";

        return Cache::remember($cacheKey, 300, function () use ($f) {
            $kpi        = $this->metricsRepo->kpiSummary($f);
            $followers  = $this->metricsRepo->followerSummary($f);
            $byPlatform = $this->metricsRepo->byPlatform($f);
            $timeSeries = $this->metricsRepo->engagementTimeSeries($f);
            $campaigns  = $this->campaignRepo->summary(
                $f->tenantId,
                $f->range->fromString(),
                $f->range->toString()
            );

            return [
                'kpi'        => $kpi,
                'followers'  => $followers,
                'by_platform'=> $byPlatform,
                'time_series'=> $timeSeries,
                'campaigns'  => $campaigns,
                'date_range' => [
                    'from'    => $f->range->fromString(),
                    'to'      => $f->range->toString(),
                    'days'    => $f->range->diffInDays(),
                ],
            ];
        });
    }

    public function invalidateCache(int $tenantId): void
    {
        Cache::forget("analytics:overview:{$tenantId}:*");
    }
}
