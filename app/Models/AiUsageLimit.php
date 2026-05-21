<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLimit extends Model
{
    protected $fillable = [
        'tenant_id', 'total_tokens_used', 'monthly_limit',
        'current_month_usage', 'reset_date', 'openai_tokens_used',
        'claude_tokens_used', 'gemini_tokens_used', 'total_cost_estimate',
        'limit_reached',
    ];

    protected function casts(): array
    {
        return [
            'reset_date'       => 'date',
            'limit_reached'    => 'boolean',
            'total_cost_estimate' => 'decimal:6',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    // ── Helpers ───────────────────────────────────────────────────

    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'monthly_limit'       => config('ai.usage.monthly_token_limit', 100_000),
                'reset_date'          => now()->startOfMonth()->addMonth()->toDateString(),
                'current_month_usage' => 0,
                'total_tokens_used'   => 0,
            ]
        );
    }

    public function usagePercent(): float
    {
        return $this->monthly_limit > 0
            ? ($this->current_month_usage / $this->monthly_limit) * 100
            : 0;
    }

    public function isNearLimit(): bool
    {
        return $this->usagePercent() >= (config('ai.usage.warning_threshold', 0.8) * 100);
    }

    public function isOverLimit(): bool
    {
        return $this->current_month_usage >= $this->monthly_limit;
    }

    public function remainingTokens(): int
    {
        return max(0, $this->monthly_limit - $this->current_month_usage);
    }

    public function needsReset(): bool
    {
        return now()->gte($this->reset_date);
    }
}
