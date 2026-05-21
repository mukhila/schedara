<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'coupon_code', 'name', 'description',
        'discount_type', 'discount_value',
        'usage_limit', 'used_count', 'per_workspace_limit',
        'applicable_plans', 'first_time_only', 'billing_cycles',
        'status', 'starts_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'applicable_plans'   => 'array',
            'first_time_only'    => 'boolean',
            'starts_at'          => 'datetime',
            'expires_at'         => 'datetime',
            'discount_value'     => 'integer',
            'usage_limit'        => 'integer',
            'used_count'         => 'integer',
            'per_workspace_limit' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ── Relationships ─────────────────────────────────────────────

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                     ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function isApplicableToPlan(string $planSlug): bool
    {
        if (empty($this->applicable_plans)) {
            return true;
        }

        return in_array($planSlug, $this->applicable_plans, true);
    }

    /** Compute the discounted amount (in cents) from a base price. */
    public function applyTo(int $amountCents): int
    {
        return match ($this->discount_type) {
            'percentage' => (int) round($amountCents * $this->discount_value / 100),
            'fixed'      => min($amountCents, $this->discount_value),
            default      => 0,
        };
    }

    public function formattedDiscount(): string
    {
        return match ($this->discount_type) {
            'percentage'      => $this->discount_value . '% off',
            'fixed'           => '$' . number_format($this->discount_value / 100, 2) . ' off',
            'trial_extension' => '+' . $this->discount_value . ' trial days',
            default           => '',
        };
    }
}
