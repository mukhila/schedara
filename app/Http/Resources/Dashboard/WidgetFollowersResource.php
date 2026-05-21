<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetFollowersResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kpi' => [
                'total_followers'   => $this['kpi']['total_followers'] ?? 0,
                'net_growth'        => $this['kpi']['net_growth'] ?? 0,
                'new_followers'     => $this['kpi']['total_new_followers'] ?? 0,
                'unfollows'         => $this['kpi']['total_unfollows'] ?? 0,
                'prior_growth_pct'  => $this['kpi']['prior_growth_pct'] ?? null,
            ],
            'time_series'     => $this['time_series'] ?? [],
            'forecast'        => $this['forecast'] ?? [],
            'by_platform'     => $this['by_platform'] ?? [],
            'fastest_growing' => $this['fastest_growing'] ?? null,
            'slowest_growing' => $this['slowest_growing'] ?? null,
            'best_day'        => $this['best_day'] ?? null,
        ];
    }
}
