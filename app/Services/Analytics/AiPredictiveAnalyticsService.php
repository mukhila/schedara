<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\PostAnalytic;
use Illuminate\Support\Facades\Cache;
use OpenAI\Laravel\Facades\OpenAI;

class AiPredictiveAnalyticsService
{
    public function predict(AnalyticsFilterDTO $f): array
    {
        $cacheKey = "analytics:ai-predict:{$f->tenantId}:{$f->range->fromString()}";

        return Cache::remember($cacheKey, 3600, function () use ($f) {
            $recentData = PostAnalytic::forTenant($f->tenantId)
                ->inRange($f->range->fromString(), $f->range->toString())
                ->selectRaw('DATE(fetched_at) AS day, AVG(engagement_rate) AS avg_er, SUM(reach) AS reach, SUM(clicks) AS clicks')
                ->groupByRaw('DATE(fetched_at)')
                ->orderBy('day')
                ->get()
                ->toArray();

            if (count($recentData) < 3) {
                return ['predictions' => [], 'insights' => [], 'note' => 'Insufficient data for predictions.'];
            }

            $prompt = $this->buildPrompt($recentData);

            try {
                $response = OpenAI::chat()->create([
                    'model'    => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a social media analytics AI. Return JSON only.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens'      => 800,
                ]);

                $result = json_decode($response->choices[0]->message->content, true) ?? [];
            } catch (\Throwable) {
                $result = ['predictions' => [], 'insights' => ['Unable to generate AI predictions at this time.']];
            }

            return $result;
        });
    }

    public function detectViralPosts(int $tenantId, float $threshold = 5.0): array
    {
        return PostAnalytic::forTenant($tenantId)
            ->where('engagement_rate', '>=', $threshold)
            ->with('post:id,uuid,content,platform')
            ->orderByDesc('engagement_rate')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function buildPrompt(array $data): string
    {
        $csv = implode("\n", array_map(
            fn ($r) => "{$r['day']},{$r['avg_er']},{$r['reach']},{$r['clicks']}",
            $data
        ));

        return <<<PROMPT
Analyze this social media performance data (day, avg_engagement_rate, reach, clicks):
{$csv}

Return JSON with:
- "predictions": array of {day, predicted_engagement_rate, predicted_reach} for the next 7 days
- "insights": array of 3-5 key insight strings
- "best_posting_time": string recommendation
- "trend": "up" | "down" | "stable"
PROMPT;
    }
}
