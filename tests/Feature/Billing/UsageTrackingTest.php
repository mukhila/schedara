<?php

namespace Tests\Feature\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\Billing\UsageLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageTrackingTest extends TestCase
{
    use RefreshDatabase;

    private Tenant           $tenant;
    private UsageLimitService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant  = Tenant::factory()->create();
        $this->service = app(UsageLimitService::class);
    }

    public function test_initialize_creates_default_tracking_rows(): void
    {
        $this->service->initializeForTenant($this->tenant->id);

        $this->assertDatabaseHas('usage_tracking', [
            'tenant_id'    => $this->tenant->id,
            'feature_name' => 'social_accounts',
        ]);
    }

    public function test_increment_increases_usage(): void
    {
        $this->service->initializeForTenant($this->tenant->id);
        $this->service->set($this->tenant->id, 'social_accounts', 0);

        $this->service->increment($this->tenant->id, 'social_accounts', 2);

        $this->assertEquals(2, $this->service->allForTenant($this->tenant->id)->get('social_accounts')->current_usage);
    }

    public function test_can_use_returns_false_when_limit_reached(): void
    {
        $this->service->initializeForTenant($this->tenant->id);
        $this->service->set($this->tenant->id, 'social_accounts', 3);

        $this->assertFalse($this->service->canUse($this->tenant->id, 'social_accounts', 1));
    }

    public function test_unlimited_feature_always_allowed(): void
    {
        $this->service->initializeForTenant($this->tenant->id);
        \App\Models\UsageTracking::where('tenant_id', $this->tenant->id)
            ->where('feature_name', 'social_accounts')
            ->update(['usage_limit' => 0, 'current_usage' => 9999]);

        $this->assertTrue($this->service->canUse($this->tenant->id, 'social_accounts', 1));
    }

    public function test_sync_from_subscription_updates_limits(): void
    {
        $plan = Plan::factory()->create([
            'limits' => ['social_accounts' => 25, 'team_members' => 10],
        ]);
        $sub  = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id'   => $plan->id,
            'provider'  => 'free',
            'provider_id' => 'free-' . $this->tenant->id,
        ]);

        $this->service->syncFromSubscription($sub);

        $this->assertDatabaseHas('usage_tracking', [
            'tenant_id'    => $this->tenant->id,
            'feature_name' => 'social_accounts',
            'usage_limit'  => 25,
        ]);
    }

    public function test_remaining_calculation(): void
    {
        $this->service->initializeForTenant($this->tenant->id);
        $this->service->set($this->tenant->id, 'social_accounts', 2);
        \App\Models\UsageTracking::where('tenant_id', $this->tenant->id)
            ->where('feature_name', 'social_accounts')
            ->update(['usage_limit' => 5]);

        $remaining = $this->service->remaining($this->tenant->id, 'social_accounts');

        $this->assertEquals(3, $remaining);
    }
}
