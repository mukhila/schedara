<?php

namespace App\Repositories\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\AccountAnalytic;
use App\Models\PostAnalytic;
use Illuminate\Support\Facades\DB;

class AnalyticsMetricsRepository
{
    // ── Overview / KPIs ──────────────────────────────────────────

    public function kpiSummary(AnalyticsFilterDTO $f): array
    {
        $q = PostAnalytic::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString());

        if ($f->platforms) {
            $q->whereIn('platform', $f->platforms);
        }

        $totals = $q->selectRaw('
            COALESCE(SUM(reach), 0)          AS total_reach,
            COALESCE(SUM(impressions), 0)    AS total_impressions,
            COALESCE(SUM(likes), 0)          AS total_likes,
            COALESCE(SUM(comments), 0)       AS total_comments,
            COALESCE(SUM(shares), 0)         AS total_shares,
            COALESCE(SUM(saves), 0)          AS total_saves,
            COALESCE(SUM(clicks), 0)         AS total_clicks,
            COALESCE(SUM(conversions), 0)    AS total_conversions,
            COALESCE(SUM(video_views), 0)    AS total_video_views,
            COALESCE(SUM(spend), 0)          AS total_spend,
            COALESCE(SUM(revenue), 0)        AS total_revenue,
            COALESCE(AVG(engagement_rate), 0) AS avg_engagement_rate,
            COUNT(*)                         AS total_posts
        ')->first();

        return $totals ? $totals->toArray() : [];
    }

    public function followerSummary(AnalyticsFilterDTO $f): array
    {
        $latest = AccountAnalytic::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->selectRaw('SUM(followers) AS total_followers, SUM(following) AS total_following, SUM(unfollows) AS total_unfollows')
            ->first();

        $growth = AccountAnalytic::forTenant($f->tenantId)
            ->where('date', '>=', $f->range->fromString())
            ->where('date', '<=', $f->range->toString())
            ->selectRaw('date, SUM(followers) AS followers')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('followers', 'date');

        $first = $growth->first() ?? 0;
        $last  = $growth->last() ?? 0;

        return [
            'total_followers'  => (int) ($latest->total_followers ?? 0),
            'total_following'  => (int) ($latest->total_following ?? 0),
            'total_unfollows'  => (int) ($latest->total_unfollows ?? 0),
            'net_growth'       => (int) ($last - $first),
            'growth_rate'      => $first > 0 ? round(($last - $first) / $first * 100, 2) : 0,
        ];
    }

    // ── Time-series ──────────────────────────────────────────────

    public function engagementTimeSeries(AnalyticsFilterDTO $f): array
    {
        $groupExpr = match ($f->groupBy) {
            'week'  => 'YEARWEEK(fetched_at, 1)',
            'month' => "DATE_FORMAT(fetched_at, '%Y-%m')",
            default => 'DATE(fetched_at)',
        };
        $labelExpr = match ($f->groupBy) {
            'week'  => "DATE_FORMAT(MIN(fetched_at), '%Y-W%u')",
            'month' => "DATE_FORMAT(fetched_at, '%Y-%m')",
            default => 'DATE(fetched_at)',
        };

        $q = PostAnalytic::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString());

        if ($f->platforms) {
            $q->whereIn('platform', $f->platforms);
        }

        return $q->selectRaw("
            {$labelExpr} AS period,
            COALESCE(SUM(likes), 0)          AS likes,
            COALESCE(SUM(comments), 0)       AS comments,
            COALESCE(SUM(shares), 0)         AS shares,
            COALESCE(SUM(saves), 0)          AS saves,
            COALESCE(SUM(reach), 0)          AS reach,
            COALESCE(SUM(impressions), 0)    AS impressions,
            COALESCE(SUM(clicks), 0)         AS clicks,
            COALESCE(AVG(engagement_rate), 0) AS engagement_rate
        ")
        ->groupByRaw($groupExpr)
        ->orderByRaw($groupExpr)
        ->get()
        ->toArray();
    }

    public function followerTimeSeries(AnalyticsFilterDTO $f): array
    {
        return AccountAnalytic::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->selectRaw('date, SUM(followers) AS followers, SUM(following) AS following, SUM(unfollows) AS unfollows')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    // ── Platform breakdown ───────────────────────────────────────

    public function byPlatform(AnalyticsFilterDTO $f): array
    {
        return PostAnalytic::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->selectRaw('
                platform,
                COALESCE(SUM(reach), 0)          AS reach,
                COALESCE(SUM(impressions), 0)    AS impressions,
                COALESCE(SUM(likes + comments + shares + saves), 0) AS engagement,
                COALESCE(SUM(clicks), 0)         AS clicks,
                COALESCE(SUM(conversions), 0)    AS conversions,
                COALESCE(AVG(engagement_rate), 0) AS engagement_rate,
                COUNT(*)                         AS posts
            ')
            ->groupBy('platform')
            ->orderByDesc('reach')
            ->get()
            ->toArray();
    }

    // ── Top posts ────────────────────────────────────────────────

    public function topPosts(AnalyticsFilterDTO $f, string $sortBy = 'engagement_rate'): array
    {
        $allowed = ['engagement_rate', 'reach', 'impressions', 'clicks', 'conversions'];
        $col     = in_array($sortBy, $allowed) ? $sortBy : 'engagement_rate';

        return PostAnalytic::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->with('post:id,uuid,content,platform,scheduled_at')
            ->orderByDesc($col)
            ->limit($f->limit)
            ->get()
            ->toArray();
    }

    // ── Click tracking ───────────────────────────────────────────

    public function clickSummary(AnalyticsFilterDTO $f): array
    {
        return DB::table('analytics_click_tracking')
            ->where('tenant_id', $f->tenantId)
            ->whereBetween('clicked_at', [$f->range->from, $f->range->to])
            ->selectRaw('
                COALESCE(SUM(clicks), 0)         AS total_clicks,
                COALESCE(SUM(unique_clicks), 0)  AS unique_clicks,
                COALESCE(SUM(conversions), 0)    AS total_conversions,
                COALESCE(SUM(revenue), 0)        AS total_revenue
            ')
            ->first();
    }
}
