<?php

namespace App\Services\Dashboard;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use App\Services\Analytics\FollowerAnalyticsService;
use Illuminate\Support\Facades\Cache;

class FollowerWidgetService
{
    public function __construct(
        private FollowerAnalyticsService   $followers,
        private AnalyticsMetricsRepository $metrics,
    ) {}

    public function get(AnalyticsFilterDTO $filter): array
    {
        return Cache::remember($this->cacheKey($filter), 300, function () use ($filter) {
            $summary    = $this->followers->summary($filter);
            $timeSeries = $this->metrics->followerTimeSeries($filter);
            $byPlatform = $this->metrics->byPlatform($filter);

            // Prior period for growth comparison
            $days        = $filter->range->diffInDays();
            $priorFilter = new AnalyticsFilterDTO(
                tenantId: $filter->tenantId,
                range:    new DateRangeDTO(
                    now()->subDays($days * 2)->toDateString(),
                    now()->subDays($days + 1)->toDateString(),
                ),
                platforms: $filter->platforms,
            );
            $priorKpi  = $this->metrics->followerSummary($priorFilter);
            $priorNet  = (int) ($priorKpi['net_growth'] ?? 0);
            $currentNet= (int) ($summary['kpi']['net_growth'] ?? 0);
            $growthPct = $priorNet !== 0 ? round(($currentNet - $priorNet) / abs($priorNet) * 100, 2) : null;

            // Best and worst performing platform by follower growth
            $byPlatformSorted = collect($byPlatform)->sortByDesc('new_followers');

            return [
                'kpi'                => array_merge($summary['kpi'] ?? [], ['prior_growth_pct' => $growthPct]),
                'time_series'        => $timeSeries,
                'by_platform'        => $byPlatform,
                'fastest_growing'    => $byPlatformSorted->first(),
                'slowest_growing'    => $byPlatformSorted->last(),
                'best_day'           => $summary['best_day'] ?? null,
                'forecast'           => $this->simpleLinearForecast($timeSeries, 7),
            ];
        });
    }

    private function simpleLinearForecast(array $timeSeries, int $daysAhead): array
    {
        if (count($timeSeries) < 2) {
            return [];
        }

        $values = array_column($timeSeries, 'new_followers');
        $n      = count($values);
        $avgGrowth = ($values[$n - 1] - $values[0]) / max(1, $n - 1);
        $lastVal   = (float) end($values);

        $forecast = [];
        for ($i = 1; $i <= $daysAhead; $i++) {
            $forecast[] = [
                'date'         => now()->addDays($i)->toDateString(),
                'new_followers'=> max(0, (int) round($lastVal + $avgGrowth * $i)),
                'forecast'     => true,
            ];
        }

        return $forecast;
    }

    private function cacheKey(AnalyticsFilterDTO $f): string
    {
        return sprintf('widget:followers:%d:%s:%s:%s',
            $f->tenantId, $f->range->fromString(), $f->range->toString(),
            implode(',', $f->platforms ?? [])
        );
    }
}
