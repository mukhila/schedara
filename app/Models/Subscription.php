<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid', 'tenant_id', 'plan_id',
        'provider', 'provider_id', 'interval', 'status',
        'current_period_start', 'current_period_end',
        'cancel_at', 'trial_ends_at', 'paused_at', 'coupon_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'current_period_start' => 'datetime',
            'current_period_end'   => 'datetime',
            'cancel_at'            => 'datetime',
            'trial_ends_at'        => 'datetime',
            'paused_at'            => 'datetime',
            'metadata'             => 'array',
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

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isStripe(): bool
    {
        return $this->provider === 'stripe';
    }

    public function isRazorpay(): bool
    {
        return $this->provider === 'razorpay';
    }

    public function isYearly(): bool
    {
        return $this->interval === 'yearly';
    }

    public function daysUntilRenewal(): int
    {
        if (! $this->current_period_end) {
            return 0;
        }

        return (int) now()->diffInDays($this->current_period_end, false);
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing';
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->trial_ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function isPaypal(): bool
    {
        return $this->provider === 'paypal';
    }
}
