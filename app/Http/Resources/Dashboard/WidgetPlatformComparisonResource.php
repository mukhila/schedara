<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetPlatformComparisonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'platforms'      => $this['platforms'] ?? [],
            'best_platform'  => $this['best_platform'] ?? null,
            'lowest_platform'=> $this['lowest_platform'] ?? null,
            'recommended'    => $this['recommended'] ?? null,
            'radar'          => $this['radar'] ?? [],
        ];
    }
}
