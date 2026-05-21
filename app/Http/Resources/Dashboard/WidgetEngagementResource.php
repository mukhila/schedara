<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetEngagementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kpi' => [
                'total_engagement'  => $this['kpi']['total_engagement'] ?? 0,
                'avg_engagement'    => $this['kpi']['avg_engagement_rate'] ?? 0,
                'total_reach'       => $this['kpi']['total_reach'] ?? 0,
                'total_impressions' => $this['kpi']['total_impressions'] ?? 0,
                'growth_pct'        => $this['kpi']['growth_pct'] ?? null,
            ],
            'breakdown'   => $this['breakdown'] ?? [],
            'time_series' => $this['time_series'] ?? [],
            'top_posts'   => $this['top_posts'] ?? [],
            'by_platform' => $this['by_platform'] ?? [],
            'best_period' => $this['best_period'] ?? null,
        ];
    }
}
