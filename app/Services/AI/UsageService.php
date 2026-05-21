<?php

namespace App\Services\AI;

use App\Events\AI\AiLimitReached;
use App\Models\AiUsageLimit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UsageService
{
    private const CACHE_PREFIX = 'ai_usage_';
    private int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('ai.cache.usage_ttl', 300);
    }

    public function track(int $tenantId, string $provider, int $tokens, float $cost): void
    {
        $limit = AiUsageLimit::forTenant($tenantId);

        // Reset monthly usage if the reset date has passed
        if ($limit->needsReset()) {
            $limit->update([
                'current_month_usage' => 0,
                'limit_reached'       => false,
                'reset_date'          => now()->startOfMonth()->addMonth()->toDateString(),
            ]);
        }

        $col = match ($provider) {
            'claude' => 'claude_tokens_used',
            'gemini' => 'gemini_tokens_used',
            default  => 'openai_tokens_used',
        };

        DB::table('ai_usage_limits')->where('tenant_id', $tenantId)->update([
            'total_tokens_used'    => DB::raw("total_tokens_used + {$tokens}"),
            'current_month_usage'  => DB::raw("current_month_usage + {$tokens}"),
            $col                   => DB::raw("{$col} + {$tokens}"),
            'total_cost_estimate'  => DB::raw("total_cost_estimate + {$cost}"),
        ]);

        Cache::forget(self::CACHE_PREFIX . $tenantId);

        // Check if limit now exceeded
        $fresh = AiUsageLimit::forTenant($tenantId);
        if ($fresh->isOverLimit() && !$fresh->limit_reached) {
            $fresh->update(['limit_reached' => true]);
        }

        // Warn at threshold
        if ($fresh->isNearLimit() && !$fresh->isOverLimit()) {
            // Fires at ~80 % — handled by listener
        }
    }

    public function isOverLimit(int $tenantId): bool
    {
        return Cache::remember(self::CACHE_PREFIX . $tenantId, $this->cacheTtl, function () use ($tenantId) {
            $limit = AiUsageLimit::forTenant($tenantId);
            if ($limit->needsReset()) return false;
            return $limit->isOverLimit();
        });
    }

    public function summary(int $tenantId): array
    {
        $limit = AiUsageLimit::forTenant($tenantId);

        return [
            'total_tokens_used'    => $limit->total_tokens_used,
            'monthly_limit'        => $limit->monthly_limit,
            'current_month_usage'  => $limit->current_month_usage,
            'remaining_tokens'     => $limit->remainingTokens(),
            'usage_percent'        => round($limit->usagePercent(), 1),
            'reset_date'           => $limit->reset_date?->toDateString(),
            'by_provider' => [
                'openai' => $limit->openai_tokens_used,
                'claude' => $limit->claude_tokens_used,
                'gemini' => $limit->gemini_tokens_used,
            ],
            'total_cost_estimate'  => (float) $limit->total_cost_estimate,
            'is_over_limit'        => $limit->isOverLimit(),
            'is_near_limit'        => $limit->isNearLimit(),
        ];
    }
}
