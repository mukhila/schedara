<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetPostPerformanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'posts'              => $this['posts'] ?? [],
            'best_post'          => $this['best_post'] ?? null,
            'worst_post'         => $this['worst_post'] ?? null,
            'viral_posts'        => $this['viral_posts'] ?? [],
            'average_engagement' => $this['average_engagement'] ?? 0,
            'total_posts'        => $this['total_posts'] ?? 0,
            'sort_by'            => $this['sort_by'] ?? 'engagement_count',
        ];
    }
}
