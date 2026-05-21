<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\AnalyticsCampaign;
use App\Models\PostAnalytic;
use Illuminate\Support\Facades\Cache;

class RoiCalculationService
{
    public function summary(AnalyticsFilterDTO $f): array
    {
        $cacheKey = "analytics:roi:{$f->tenantId}:{$f->range->fromString()}:{$f->range->toString()}";

        return Cache::remember($cacheKey, 300, function () use ($f) {
            // Post-level spend / revenue
            $postTotals = PostAnalytic::forTenant($f->tenantId)
                ->inRange($f->range->fromString(), $f->range->toString())
                ->selectRaw('
                    COALESCE(SUM(spend), 0)    AS total_spend,
                    COALESCE(SUM(revenue), 0)  AS total_revenue,
                    COALESCE(SUM(conversions), 0) AS total_conversions,
                    COALESCE(SUM(clicks), 0)   AS total_clicks
                ')
                ->first();

            // Campaign-level aggregates
            $campaignTotals = AnalyticsCampaign::forTenant($f->tenantId)
                ->inRange($f->range->fromString(), $f->range->toString())
                ->selectRaw('
                    COALESCE(SUM(spend), 0)    AS total_spend,
                    COALESCE(SUM(revenue), 0)  AS total_revenue,
                    COALESCE(SUM(conversions), 0) AS total_conversions,
                    COALESCE(AVG(roi), 0)      AS avg_roi,
                    COALESCE(AVG(roas), 0)     AS avg_roas,
                    COALESCE(AVG(ctr), 0)      AS avg_ctr,
                    COALESCE(AVG(cpc), 0)      AS avg_cpc
                ')
                ->first();

            $totalSpend   = (float) ($postTotals->total_spend ?? 0) + (float) ($campaignTotals->total_spend ?? 0);
            $totalRevenue = (float) ($postTotals->total_revenue ?? 0) + (float) ($campaignTotals->total_revenue ?? 0);

            return [
                'total_spend'      => $totalSpend,
                'total_revenue'    => $totalRevenue,
                'net_profit'       => $totalRevenue - $totalSpend,
                'roi'              => $totalSpend > 0 ? round(($totalRevenue - $totalSpend) / $totalSpend * 100, 2) : 0,
                'roas'             => $totalSpend > 0 ? round($totalRevenue / $totalSpend, 4) : 0,
                'total_conversions'=> (int) (($postTotals->total_conversions ?? 0) + ($campaignTotals->total_conversions ?? 0)),
                'cpa'              => ($campaignTotals->total_conversions ?? 0) > 0
                    ? round($totalSpend / $campaignTotals->total_conversions, 4) : 0,
                'campaigns'        => [
                    'avg_roi'  => round((float) ($campaignTotals->avg_roi ?? 0), 2),
                    'avg_roas' => round((float) ($campaignTotals->avg_roas ?? 0), 4),
                    'avg_ctr'  => round((float) ($campaignTotals->avg_ctr ?? 0), 4),
                    'avg_cpc'  => round((float) ($campaignTotals->avg_cpc ?? 0), 4),
                ],
            ];
        });
    }

    public function byPlatform(AnalyticsFilterDTO $f): array
    {
        return AnalyticsCampaign::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->whereNotNull('platform')
            ->selectRaw('platform, SUM(spend) AS spend, SUM(revenue) AS revenue, AVG(roi) AS avg_roi, SUM(conversions) AS conversions')
            ->groupBy('platform')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }
}
