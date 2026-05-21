<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\PostAnalytic;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use Illuminate\Support\Facades\Cache;

class EngagementAnalyticsService
{
    public function __construct(private AnalyticsMetricsRepository $repo) {}

    public function summary(AnalyticsFilterDTO $f): array
    {
        $cacheKey = "analytics:engagement:{$f->tenantId}:{$f->range->fromString()}:{$f->range->toString()}";

        return Cache::remember($cacheKey, 300, function () use ($f) {
            $timeSeries = $this->repo->engagementTimeSeries($f);
            $kpi        = $this->repo->kpiSummary($f);
            $byPlatform = $this->repo->byPlatform($f);
            $topPosts   = $this->repo->topPosts($f, 'engagement_rate');

            // Engagement breakdown
            $totalEngagement = ($kpi['total_likes'] ?? 0)
                + ($kpi['total_comments'] ?? 0)
                + ($kpi['total_shares'] ?? 0)
                + ($kpi['total_saves'] ?? 0);

            $breakdown = [];
            foreach (['likes', 'comments', 'shares', 'saves'] as $type) {
                $val = (int) ($kpi["total_{$type}"] ?? 0);
                $breakdown[$type] = [
                    'count'      => $val,
                    'percentage' => $totalEngagement > 0 ? round($val / $totalEngagement * 100, 2) : 0,
                ];
            }

            return [
                'kpi'         => $kpi,
                'breakdown'   => $breakdown,
                'by_platform' => $byPlatform,
                'time_series' => $timeSeries,
                'top_posts'   => array_slice($topPosts, 0, 10),
            ];
        });
    }

    public function rateByPost(int $tenantId, int $postId): float
    {
        $analytic = PostAnalytic::where('tenant_id', $tenantId)
            ->where('post_id', $postId)
            ->latest('fetched_at')
            ->first();

        return $analytic?->computedEngagementRate() ?? 0.0;
    }
}
