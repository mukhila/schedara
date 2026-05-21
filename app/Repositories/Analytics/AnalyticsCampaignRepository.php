<?php

namespace App\Repositories\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\AnalyticsCampaign;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AnalyticsCampaignRepository
{
    public function paginate(AnalyticsFilterDTO $f, int $perPage = 20): LengthAwarePaginator
    {
        $q = AnalyticsCampaign::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->with('creator:id,name');

        if ($f->platforms) {
            $q->whereIn('platform', $f->platforms);
        }

        return $q->orderByDesc('start_date')->paginate($perPage);
    }

    public function summary(int $tenantId, string $from, string $to): array
    {
        return AnalyticsCampaign::forTenant($tenantId)
            ->inRange($from, $to)
            ->selectRaw('
                COUNT(*)                            AS total_campaigns,
                COALESCE(SUM(budget), 0)            AS total_budget,
                COALESCE(SUM(spend), 0)             AS total_spend,
                COALESCE(SUM(revenue), 0)           AS total_revenue,
                COALESCE(SUM(impressions), 0)       AS total_impressions,
                COALESCE(SUM(clicks), 0)            AS total_clicks,
                COALESCE(SUM(conversions), 0)       AS total_conversions,
                COALESCE(AVG(roi), 0)               AS avg_roi,
                COALESCE(AVG(roas), 0)              AS avg_roas,
                COALESCE(AVG(ctr), 0)               AS avg_ctr
            ')
            ->first()
            ?->toArray() ?? [];
    }

    public function findByUuid(string $uuid): AnalyticsCampaign
    {
        return AnalyticsCampaign::where('uuid', $uuid)->with('creator')->firstOrFail();
    }

    public function performingByROI(int $tenantId, int $limit = 5): Collection
    {
        return AnalyticsCampaign::forTenant($tenantId)
            ->completed()
            ->orderByDesc('roi')
            ->limit($limit)
            ->get();
    }
}
