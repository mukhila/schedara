<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'slug', 'description',
        'stripe_product_id',
        'stripe_monthly_price_id', 'stripe_yearly_price_id',
        'razorpay_monthly_plan_id', 'razorpay_yearly_plan_id',
        'paypal_monthly_plan_id', 'paypal_yearly_plan_id',
        'price_monthly', 'price_yearly',
        'features', 'limits',
        'trial_days', 'is_active', 'is_popular', 'sort_order', 'currency',
    ];

    protected function casts(): array
    {
        return [
            'features'      => 'array',
            'limits'        => 'array',
            'price_monthly' => 'integer',
            'price_yearly'  => 'integer',
            'is_active'     => 'boolean',
            'is_popular'    => 'boolean',
            'trial_days'    => 'integer',
            'sort_order'    => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(SubscriptionFeature::class)->orderBy('sort_order');
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Stripe helpers ───────────────────────────────────────────

    public function stripePriceId(string $interval): ?string
    {
        return $interval === 'yearly'
            ? $this->stripe_yearly_price_id
            : $this->stripe_monthly_price_id;
    }

    public function razorpayPlanId(string $interval): ?string
    {
        return $interval === 'yearly'
            ? $this->razorpay_yearly_plan_id
            : $this->razorpay_monthly_plan_id;
    }

    public function paypalPlanId(string $interval): ?string
    {
        return $interval === 'yearly'
            ? $this->paypal_yearly_plan_id
            : $this->paypal_monthly_plan_id;
    }

    public function isFree(): bool
    {
        return $this->price_monthly === 0 && $this->price_yearly === 0;
    }

    public function priceForInterval(string $interval): int
    {
        return $interval === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }

    public function currencySymbol(): string
    {
        return match (strtolower($this->currency ?? 'usd')) {
            'inr'   => '₹',
            'eur'   => '€',
            'gbp'   => '£',
            'aed'   => 'د.إ',
            default => '$',
        };
    }

    public function hasTrialDays(): bool
    {
        return ($this->trial_days ?? 0) > 0;
    }

    // ── Feature/limit helpers ────────────────────────────────────

    public function hasFeature(string $feature): bool
    {
        return (bool) data_get($this->features, $feature, false);
    }

    public function getLimit(string $key, mixed $default = null): mixed
    {
        return data_get($this->limits, $key, $default);
    }

    /** Price in major currency units (dollars/rupees). */
    public function monthlyPrice(): float
    {
        return $this->price_monthly / 100;
    }

    public function yearlyPrice(): float
    {
        return $this->price_yearly / 100;
    }

    public function yearlyDiscount(): float
    {
        if ($this->price_monthly === 0) {
            return 0;
        }

        $monthly12 = $this->price_monthly * 12;

        return round((1 - $this->price_yearly / $monthly12) * 100, 1);
    }
}
