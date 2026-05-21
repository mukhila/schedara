<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\UsageTracking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class UsageLimitService
{
    // Features that reset monthly
    private const MONTHLY_RESET_FEATURES = ['scheduled_posts', 'ai_credits', 'analytics_reports'];

    // Feature key → default limit (0 = unlimited) for the free plan
    private const FREE_PLAN_DEFAULTS = [
        'social_accounts'   => 3,
        'scheduled_posts'   => 30,
        'team_members'      => 1,
        'storage_mb'        => 512,
        'ai_credits'        => 0,
        'analytics_reports' => 5,
    ];

    public function canUse(int $tenantId, string $feature, int $amount = 1): bool
    {
        $tracking = $this->getTracking($tenantId, $feature);

        if ($tracking === null || $tracking->isUnlimited()) {
            return true;
        }

        $this->resetIfNeeded($tracking);

        return ($tracking->current_usage + $amount) <= $tracking->usage_limit;
    }

    public function increment(int $tenantId, string $feature, int $amount = 1): void
    {
        $tracking = $this->getOrCreateTracking($tenantId, $feature);
        $this->resetIfNeeded($tracking);
        $tracking->increment('current_usage', $amount);
        $this->bustCache($tenantId, $feature);
    }

    public function decrement(int $tenantId, string $feature, int $amount = 1): void
    {
        $tracking = $this->getTracking($tenantId, $feature);
        if ($tracking) {
            $tracking->decrement('current_usage', min($amount, $tracking->current_usage));
            $this->bustCache($tenantId, $feature);
        }
    }

    public function set(int $tenantId, string $feature, int $value): void
    {
        $tracking = $this->getOrCreateTracking($tenantId, $feature);
        $tracking->update(['current_usage' => max(0, $value)]);
        $this->bustCache($tenantId, $feature);
    }

    public function remaining(int $tenantId, string $feature): ?int
    {
        $tracking = $this->getTracking($tenantId, $feature);

        return $tracking?->remaining();
    }

    /** Sync usage limits for all tracked features when a subscription changes. */
    public function syncFromSubscription(Subscription $subscription): void
    {
        $plan   = $subscription->plan;
        $limits = $plan->limits ?? [];

        foreach ($limits as $feature => $limit) {
            $tracking = UsageTracking::firstOrNew([
                'tenant_id'    => $subscription->tenant_id,
                'feature_name' => $feature,
            ]);

            $tracking->usage_limit = (int) $limit;

            if (! $tracking->reset_date && in_array($feature, self::MONTHLY_RESET_FEATURES, true)) {
                $tracking->reset_date = now()->startOfMonth()->addMonth();
            }

            $tracking->save();
        }
    }

    /** Initialize usage tracking for a new tenant with free plan defaults. */
    public function initializeForTenant(int $tenantId): void
    {
        foreach (self::FREE_PLAN_DEFAULTS as $feature => $limit) {
            UsageTracking::firstOrCreate(
                ['tenant_id' => $tenantId, 'feature_name' => $feature],
                [
                    'current_usage' => 0,
                    'usage_limit'   => $limit,
                    'reset_date'    => in_array($feature, self::MONTHLY_RESET_FEATURES, true)
                        ? now()->startOfMonth()->addMonth()
                        : null,
                ]
            );
        }
    }

    /** Get all usage for a tenant as a keyed collection. */
    public function allForTenant(int $tenantId): Collection
    {
        return UsageTracking::where('tenant_id', $tenantId)->get()->keyBy('feature_name');
    }

    private function getTracking(int $tenantId, string $feature): ?UsageTracking
    {
        return Cache::remember(
            "usage:{$tenantId}:{$feature}",
            60,
            fn () => UsageTracking::where('tenant_id', $tenantId)->where('feature_name', $feature)->first()
        );
    }

    private function getOrCreateTracking(int $tenantId, string $feature): UsageTracking
    {
        return UsageTracking::firstOrCreate(
            ['tenant_id' => $tenantId, 'feature_name' => $feature],
            ['current_usage' => 0, 'usage_limit' => 0]
        );
    }

    private function resetIfNeeded(UsageTracking $tracking): void
    {
        if ($tracking->reset_date && $tracking->reset_date->isPast()) {
            $tracking->update([
                'current_usage' => 0,
                'reset_date'    => now()->startOfMonth()->addMonth(),
            ]);
        }
    }

    private function bustCache(int $tenantId, string $feature): void
    {
        Cache::forget("usage:{$tenantId}:{$feature}");
    }
}
