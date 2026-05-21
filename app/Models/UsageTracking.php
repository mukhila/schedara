<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageTracking extends Model
{
    protected $table = 'usage_tracking';

    protected $fillable = [
        'tenant_id', 'feature_name', 'current_usage', 'usage_limit', 'reset_date',
    ];

    protected function casts(): array
    {
        return [
            'current_usage' => 'integer',
            'usage_limit'   => 'integer',
            'reset_date'    => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isUnlimited(): bool
    {
        return $this->usage_limit === 0;
    }

    public function remaining(): ?int
    {
        if ($this->isUnlimited()) {
            return null;
        }

        return max(0, $this->usage_limit - $this->current_usage);
    }

    public function isExhausted(): bool
    {
        if ($this->isUnlimited()) {
            return false;
        }

        return $this->current_usage >= $this->usage_limit;
    }

    public function percentageUsed(): float
    {
        if ($this->isUnlimited() || $this->usage_limit === 0) {
            return 0.0;
        }

        return min(100.0, round($this->current_usage / $this->usage_limit * 100, 1));
    }

    /** True if usage is at or above 80% of the limit. */
    public function isNearLimit(int $threshold = 80): bool
    {
        return !$this->isUnlimited() && $this->percentageUsed() >= $threshold;
    }
}
