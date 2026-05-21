<?php

namespace App\Services\Dashboard;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use App\Services\Analytics\EngagementAnalyticsService;
use Illuminate\Support\Facades\Cache;

class EngagementWidgetService
{
    public function __construct(
        private EngagementAnalyticsService   $engagement,
        private AnalyticsMetricsRepository   $metrics,
    ) {}

    public function get(AnalyticsFilterDTO $filter): array
    {
        $cacheKey = $this->cacheKey($filter);

        return Cache::remember($cacheKey, 300, function () use ($filter) {
            $summary    = $this->engagement->summary($filter);
            $timeSeries = $this->metrics->engagementTimeSeries($filter);
            $topPosts   = $this->metrics->topPosts($filter, 'engagement_count');

            // Compute growth % vs prior period
            $priorFilter = $this->priorPeriod($filter);
            $priorKpi    = $this->metrics->kpiSummary($priorFilter);
            $currentKpi  = $summary['kpi'] ?? [];

            $priorEng   = (float) ($priorKpi['total_engagement'] ?? 0);
            $currentEng = (float) ($currentKpi['total_engagement'] ?? 0);
            $growthPct  = $priorEng > 0 ? round(($currentEng - $priorEng) / $priorEng * 100, 2) : null;

            return [
                'kpi' => array_merge($currentKpi, ['growth_pct' => $growthPct]),
                'breakdown'    => $summary['breakdown'] ?? [],
                'time_series'  => $timeSeries,
                'top_posts'    => $topPosts,
                'by_platform'  => $summary['by_platform'] ?? [],
                'best_period'  => $this->bestPeriod($timeSeries),
            ];
        });
    }

    private function bestPeriod(array $timeSeries): ?array
    {
        if (empty($timeSeries)) {
            return null;
        }
        return collect($timeSeries)->sortByDesc('engagement_count')->first();
    }

    private function priorPeriod(AnalyticsFilterDTO $f): AnalyticsFilterDTO
    {
        $days = $f->range->diffInDays();
        return new AnalyticsFilterDTO(
            tenantId:  $f->tenantId,
            range:     new \App\DTOs\Analytics\DateRangeDTO(
                now()->subDays($days * 2)->toDateString(),
                now()->subDays($days + 1)->toDateString(),
            ),
            platforms: $f->platforms,
        );
    }

    private function cacheKey(AnalyticsFilterDTO $f): string
    {
        return sprintf('widget:engagement:%d:%s:%s:%s',
            $f->tenantId, $f->range->fromString(), $f->range->toString(),
            implode(',', $f->platforms ?? [])
        );
    }
}
