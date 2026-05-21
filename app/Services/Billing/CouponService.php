<?php

namespace App\Services\Billing;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Subscription;
use App\Models\Tenant;

class CouponService
{
    /**
     * Validate a coupon code for a given tenant and plan, returning the Coupon or throwing.
     */
    public function validate(string $code, Tenant $tenant, string $planSlug, string $billingCycle): Coupon
    {
        $coupon = Coupon::where('coupon_code', strtoupper($code))->first();

        if (! $coupon) {
            throw new \RuntimeException('Invalid coupon code.');
        }
        if (! $coupon->isValid()) {
            throw new \RuntimeException('This coupon is expired or no longer active.');
        }
        if (! $coupon->isApplicableToPlan($planSlug)) {
            throw new \RuntimeException('This coupon is not applicable to the selected plan.');
        }
        if ($coupon->billing_cycles !== 'both' && $coupon->billing_cycles !== $billingCycle) {
            throw new \RuntimeException("This coupon is only valid for {$coupon->billing_cycles} billing.");
        }

        $redeemCount = CouponRedemption::where('coupon_id', $coupon->id)
                                        ->where('tenant_id', $tenant->id)
                                        ->count();

        if ($redeemCount >= $coupon->per_workspace_limit) {
            throw new \RuntimeException('You have already used this coupon.');
        }

        if ($coupon->first_time_only) {
            $hasExistingSubscription = Subscription::where('tenant_id', $tenant->id)
                ->whereIn('status', ['active', 'trialing', 'past_due'])
                ->exists();

            if ($hasExistingSubscription) {
                throw new \RuntimeException('This coupon is only for first-time subscribers.');
            }
        }

        return $coupon;
    }

    /**
     * Apply the coupon: record redemption, increment used_count.
     */
    public function redeem(Coupon $coupon, Tenant $tenant, ?Subscription $subscription = null): CouponRedemption
    {
        $redemption = CouponRedemption::create([
            'coupon_id'       => $coupon->id,
            'tenant_id'       => $tenant->id,
            'subscription_id' => $subscription?->id,
            'redeemed_at'     => now(),
        ]);

        $coupon->increment('used_count');

        return $redemption;
    }

    /**
     * Calculate discounted price in cents.
     * Returns ['original' => int, 'discount' => int, 'final' => int].
     */
    public function calculatePrice(int $originalCents, Coupon $coupon): array
    {
        if ($coupon->discount_type === 'trial_extension') {
            return ['original' => $originalCents, 'discount' => 0, 'final' => $originalCents];
        }

        $discount = $coupon->applyTo($originalCents);
        $final    = max(0, $originalCents - $discount);

        return ['original' => $originalCents, 'discount' => $discount, 'final' => $final];
    }

    /**
     * Get extra trial days from a trial_extension coupon.
     */
    public function extraTrialDays(Coupon $coupon): int
    {
        return $coupon->discount_type === 'trial_extension' ? (int) $coupon->discount_value : 0;
    }
}
