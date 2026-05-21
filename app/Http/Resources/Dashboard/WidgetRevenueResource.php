<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetRevenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $kpi = $this['kpi'] ?? [];
        return [
            'kpi' => [
                'total_revenue'      => $kpi['total_revenue'] ?? 0,
                'total_spend'        => $kpi['total_spend'] ?? 0,
                'net_profit'         => ($kpi['total_revenue'] ?? 0) - ($kpi['total_spend'] ?? 0),
                'roi'                => $kpi['roi'] ?? 0,
                'roas'               => $kpi['roas'] ?? 0,
                'cpa'                => $kpi['cpa'] ?? 0,
                'revenue_growth_pct' => $kpi['revenue_growth_pct'] ?? null,
            ],
            'by_platform'  => $this['by_platform'] ?? [],
            'time_series'  => $this['time_series'] ?? [],
            'top_platform' => $this['top_platform'] ?? null,
            'prior_period' => $this['prior_period'] ?? [],
        ];
    }
}
