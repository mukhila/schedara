<?php

namespace App\Services\Dashboard;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use Illuminate\Support\Facades\Cache;

class PostPerformanceService
{
    public function __construct(private AnalyticsMetricsRepository $metrics) {}

    public function get(AnalyticsFilterDTO $filter, string $sortBy = 'engagement_count'): array
    {
        $allowed = ['engagement_count', 'reach_count', 'clicks', 'conversions', 'impressions'];
        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'engagement_count';
        }

        $cacheKey = sprintf('widget:post-perf:%d:%s:%s:%s:%s',
            $filter->tenantId, $filter->range->fromString(), $filter->range->toString(),
            implode(',', $filter->platforms ?? []), $sortBy
        );

        return Cache::remember($cacheKey, 300, function () use ($filter, $sortBy) {
            $posts  = $this->metrics->topPosts($filter, $sortBy);
            $avgKpi = $this->metrics->kpiSummary($filter);

            $postList = collect($posts);
            $best     = $postList->first();
            $worst    = $postList->last();

            // Compute a simple performance score (0-100) relative to best
            $maxEngagement = (float) ($best['engagement_count'] ?? 1);
            $scored = $postList->map(function ($post) use ($maxEngagement) {
                $score = $maxEngagement > 0
                    ? min(100, (int) round($post['engagement_count'] / $maxEngagement * 100))
                    : 0;
                return array_merge($post, ['performance_score' => $score]);
            });

            // Viral posts: posts with engagement_rate > threshold
            $viralThreshold = config('analytics.viral_threshold', 5.0);
            $viralPosts = $scored->filter(fn ($p) => ($p['engagement_rate'] ?? 0) >= $viralThreshold)->values();

            return [
                'posts'             => $scored->values()->toArray(),
                'best_post'         => $best,
                'worst_post'        => $worst,
                'viral_posts'       => $viralPosts->toArray(),
                'average_engagement'=> (float) ($avgKpi['avg_engagement_rate'] ?? 0),
                'total_posts'       => $scored->count(),
                'sort_by'           => $sortBy,
            ];
        });
    }
}
