<?php

namespace App\Services\Dashboard;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use App\Services\Analytics\RoiCalculationService;
use Illuminate\Support\Facades\Cache;

class RevenueInsightService
{
    public function __construct(
        private RoiCalculationService      $roi,
        private AnalyticsMetricsRepository $metrics,
    ) {}

    public function get(AnalyticsFilterDTO $filter): array
    {
        $cacheKey = sprintf('widget:revenue:%d:%s:%s:%s',
            $filter->tenantId, $filter->range->fromString(), $filter->range->toString(),
            implode(',', $filter->platforms ?? [])
        );

        return Cache::remember($cacheKey, 300, function () use ($filter) {
            $summary    = $this->roi->summary($filter);
            $byPlatform = $this->roi->byPlatform($filter);

            // Prior period
            $days        = $filter->range->diffInDays();
            $priorFilter = new AnalyticsFilterDTO(
                tenantId: $filter->tenantId,
                range:    new DateRangeDTO(
                    now()->subDays($days * 2)->toDateString(),
                    now()->subDays($days + 1)->toDateString(),
                ),
                platforms: $filter->platforms,
            );
            $priorSummary = $this->roi->summary($priorFilter);

            $priorRevenue  = (float) ($priorSummary['total_revenue'] ?? 0);
            $currentRevenue= (float) ($summary['total_revenue'] ?? 0);
            $revenueGrowth = $priorRevenue > 0
                ? round(($currentRevenue - $priorRevenue) / $priorRevenue * 100, 2)
                : null;

            // Time-series revenue
            $timeSeries = $this->metrics->engagementTimeSeries($filter);
            $revenueTs  = collect($timeSeries)->map(fn ($row) => [
                'date'    => $row['metric_date'] ?? $row['date'] ?? null,
                'revenue' => (float) ($row['revenue'] ?? 0),
                'spend'   => (float) ($row['spend'] ?? 0),
                'profit'  => (float) (($row['revenue'] ?? 0) - ($row['spend'] ?? 0)),
            ])->values()->toArray();

            // Top revenue platform
            $topPlatform = collect($byPlatform)->sortByDesc('revenue')->first();

            return [
                'kpi'            => array_merge($summary, ['revenue_growth_pct' => $revenueGrowth]),
                'prior_period'   => $priorSummary,
                'by_platform'    => $byPlatform,
                'time_series'    => $revenueTs,
                'top_platform'   => $topPlatform,
            ];
        });
    }
}
