<?php

namespace Tests\Feature\Billing;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Billing\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private Tenant        $tenant;
    private Plan          $plan;
    private CouponService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant  = Tenant::factory()->create();
        $this->plan    = Plan::factory()->create(['slug' => 'starter']);
        $this->service = app(CouponService::class);
    }

    public function test_valid_percentage_coupon_applies(): void
    {
        $coupon = Coupon::factory()->create([
            'coupon_code'    => 'SAVE20',
            'discount_type'  => 'percentage',
            'discount_value' => 20,
            'status'         => 'active',
        ]);

        $validated = $this->service->validate('SAVE20', $this->tenant, $this->plan->slug, 'monthly');
        $pricing   = $this->service->calculatePrice(2900, $validated);

        $this->assertEquals(2900, $pricing['original']);
        $this->assertEquals(580,  $pricing['discount']); // 20% of 2900
        $this->assertEquals(2320, $pricing['final']);
    }

    public function test_fixed_coupon_applies(): void
    {
        $coupon = Coupon::factory()->create([
            'coupon_code'    => 'FLAT500',
            'discount_type'  => 'fixed',
            'discount_value' => 500,
            'status'         => 'active',
        ]);

        $validated = $this->service->validate('FLAT500', $this->tenant, $this->plan->slug, 'monthly');
        $pricing   = $this->service->calculatePrice(2900, $validated);

        $this->assertEquals(500,  $pricing['discount']);
        $this->assertEquals(2400, $pricing['final']);
    }

    public function test_expired_coupon_throws(): void
    {
        Coupon::factory()->create([
            'coupon_code' => 'OLD',
            'expires_at'  => now()->subDay(),
            'status'      => 'active',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->service->validate('OLD', $this->tenant, $this->plan->slug, 'monthly');
    }

    public function test_invalid_coupon_code_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->service->validate('NONEXISTENT', $this->tenant, $this->plan->slug, 'monthly');
    }

    public function test_plan_restricted_coupon_blocks_wrong_plan(): void
    {
        Coupon::factory()->create([
            'coupon_code'      => 'AGENCYONLY',
            'applicable_plans' => ['agency'],
            'status'           => 'active',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->service->validate('AGENCYONLY', $this->tenant, 'starter', 'monthly');
    }

    public function test_redeem_increments_used_count(): void
    {
        $coupon = Coupon::factory()->create([
            'coupon_code'    => 'INCR',
            'discount_type'  => 'percentage',
            'discount_value' => 10,
            'status'         => 'active',
            'used_count'     => 0,
        ]);

        $this->service->redeem($coupon, $this->tenant);

        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_trial_extension_coupon_returns_extra_days(): void
    {
        $coupon = Coupon::factory()->create([
            'coupon_code'    => 'TRIAL7',
            'discount_type'  => 'trial_extension',
            'discount_value' => 7,
            'status'         => 'active',
        ]);

        $validated = $this->service->validate('TRIAL7', $this->tenant, $this->plan->slug, 'monthly');
        $extra     = $this->service->extraTrialDays($validated);

        $this->assertEquals(7, $extra);
    }
}
