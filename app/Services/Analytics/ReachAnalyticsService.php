<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use Illuminate\Support\Facades\Cache;

class ReachAnalyticsService
{
    public function __construct(private AnalyticsMetricsRepository $repo) {}

    public function summary(AnalyticsFilterDTO $f): array
    {
        $cacheKey = "analytics:reach:{$f->tenantId}:{$f->range->fromString()}:{$f->range->toString()}";

        return Cache::remember($cacheKey, 300, function () use ($f) {
            $kpi        = $this->repo->kpiSummary($f);
            $timeSeries = $this->repo->engagementTimeSeries($f);
            $byPlatform = $this->repo->byPlatform($f);

            $reach       = (int) ($kpi['total_reach'] ?? 0);
            $impressions = (int) ($kpi['total_impressions'] ?? 0);
            $frequency   = $reach > 0 ? round($impressions / $reach, 2) : 0;
            $ctr         = $impressions > 0
                ? round(($kpi['total_clicks'] ?? 0) / $impressions * 100, 4)
                : 0;

            return [
                'kpi' => [
                    'reach'       => $reach,
                    'impressions' => $impressions,
                    'frequency'   => $frequency,
                    'ctr'         => $ctr,
                    'clicks'      => (int) ($kpi['total_clicks'] ?? 0),
                    'video_views' => (int) ($kpi['total_video_views'] ?? 0),
                ],
                'by_platform' => $byPlatform,
                'time_series' => $timeSeries,
            ];
        });
    }
}
