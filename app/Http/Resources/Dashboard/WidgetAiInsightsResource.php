<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetAiInsightsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'forecast'      => $this['forecast'] ?? [],
            'viral_posts'   => $this['viral_posts'] ?? [],
            'insights'      => $this['insights'] ?? [],
            'best_time'     => $this['best_time'] ?? null,
            'generated_at'  => $this['generated_at'] ?? now()->toIso8601String(),
            'ai_enabled'    => (bool) config('analytics.ai_analysis_enabled', false),
        ];
    }
}
