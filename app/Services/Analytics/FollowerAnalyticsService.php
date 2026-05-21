<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\AccountAnalytic;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use Illuminate\Support\Facades\Cache;

class FollowerAnalyticsService
{
    public function __construct(private AnalyticsMetricsRepository $repo) {}

    public function summary(AnalyticsFilterDTO $f): array
    {
        $cacheKey = "analytics:followers:{$f->tenantId}:{$f->range->fromString()}:{$f->range->toString()}";

        return Cache::remember($cacheKey, 300, function () use ($f) {
            $timeSeries = $this->repo->followerTimeSeries($f);
            $kpi        = $this->repo->followerSummary($f);

            // Per-platform follower breakdown from account_analytics
            $byPlatform = AccountAnalytic::forTenant($f->tenantId)
                ->inRange($f->range->fromString(), $f->range->toString())
                ->join('social_accounts', 'account_analytics.social_account_id', '=', 'social_accounts.id')
                ->selectRaw('social_accounts.platform, SUM(account_analytics.followers) AS followers, SUM(account_analytics.unfollows) AS unfollows')
                ->groupBy('social_accounts.platform')
                ->orderByDesc('followers')
                ->get()
                ->toArray();

            // Growth milestones — highlight the best day
            $bestDay = collect($timeSeries)->sortByDesc('followers')->first();

            return [
                'kpi'         => $kpi,
                'by_platform' => $byPlatform,
                'time_series' => $timeSeries,
                'best_day'    => $bestDay,
            ];
        });
    }

    public function growthRate(int $tenantId, int $days = 30): float
    {
        $range = \App\DTOs\Analytics\DateRangeDTO::lastDays($days);

        $series = AccountAnalytic::forTenant($tenantId)
            ->inRange($range->fromString(), $range->toString())
            ->selectRaw('date, SUM(followers) AS followers')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('followers', 'date');

        $first = $series->first();
        $last  = $series->last();

        if (!$first || $first == 0) {
            return 0.0;
        }

        return round(($last - $first) / $first * 100, 2);
    }
}
