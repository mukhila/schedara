<?php

namespace App\Services\Dashboard;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use App\Services\Analytics\RoiCalculationService;
use Illuminate\Support\Facades\Cache;

class PlatformComparisonService
{
    public function __construct(
        private AnalyticsMetricsRepository $metrics,
        private RoiCalculationService      $roi,
    ) {}

    public function get(AnalyticsFilterDTO $filter): array
    {
        $cacheKey = sprintf('widget:platform-comparison:%d:%s:%s',
            $filter->tenantId, $filter->range->fromString(), $filter->range->toString()
        );

        return Cache::remember($cacheKey, 300, function () use ($filter) {
            $byPlatform = $this->metrics->byPlatform($filter);
            $roiByPlatform = $this->roi->byPlatform($filter);

            // Merge ROI data into platform list
            $roiMap = collect($roiByPlatform)->keyBy('platform');
            $platforms = collect($byPlatform)->map(function ($p) use ($roiMap) {
                $roi = $roiMap->get($p['platform'] ?? '', []);
                return array_merge($p, [
                    'roi'      => $roi['roi'] ?? 0,
                    'roas'     => $roi['roas'] ?? 0,
                    'revenue'  => $roi['revenue'] ?? 0,
                    'spend'    => $roi['spend'] ?? 0,
                ]);
            });

            // Rank platforms
            $ranked = $platforms->sortByDesc('engagement_count')->values();
            $best   = $ranked->first();
            $lowest = $ranked->last();

            // AI recommendation: highest-growth, lowest-engagement platforms
            $recommended = $platforms
                ->sortByDesc(fn ($p) => ($p['new_followers'] ?? 0) / max(1, $p['followers'] ?? 1))
                ->first();

            // Radar chart data (normalized 0-100 per metric)
            $radarData = $this->buildRadarData($platforms->toArray());

            return [
                'platforms'         => $ranked->toArray(),
                'best_platform'     => $best,
                'lowest_platform'   => $lowest,
                'recommended'       => $recommended,
                'radar'             => $radarData,
            ];
        });
    }

    private function buildRadarData(array $platforms): array
    {
        $metrics = ['impressions', 'reach_count', 'engagement_count', 'new_followers', 'clicks', 'revenue'];
        $maxes   = [];

        foreach ($metrics as $m) {
            $maxes[$m] = max(1, collect($platforms)->max($m) ?? 1);
        }

        return collect($platforms)->map(function ($p) use ($metrics, $maxes) {
            $scores = [];
            foreach ($metrics as $m) {
                $scores[$m] = round(($p[$m] ?? 0) / $maxes[$m] * 100, 1);
            }
            return ['platform' => $p['platform'], 'scores' => $scores];
        })->values()->toArray();
    }
}
