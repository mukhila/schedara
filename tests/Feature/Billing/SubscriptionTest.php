<?php

namespace Tests\Feature\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User   $user;
    private Plan   $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user   = User::factory()->create();
        $this->tenant = Tenant::factory()->create(['owner_id' => $this->user->id]);
        $this->plan   = Plan::factory()->create([
            'slug'          => 'starter',
            'price_monthly' => 2900,
            'trial_days'    => 14,
            'is_active'     => true,
        ]);
    }

    public function test_activate_free_subscription(): void
    {
        $freePlan = Plan::factory()->create(['slug' => 'free', 'price_monthly' => 0, 'trial_days' => 0]);
        $service  = app(SubscriptionService::class);

        $sub = $service->activateFree($this->tenant, $freePlan);

        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan_id'   => $freePlan->id,
            'status'    => 'active',
        ]);
        $this->assertTrue($sub->isActive());
    }

    public function test_activate_free_plan_with_trial(): void
    {
        $service = app(SubscriptionService::class);

        $sub = $service->activateFree($this->tenant, $this->plan);

        $this->assertEquals('trialing', $sub->status);
        $this->assertNotNull($sub->trial_ends_at);
        $this->assertTrue($sub->isOnTrial());
        $this->assertEquals(14, $sub->trialDaysRemaining());
    }

    public function test_cancel_subscription_at_period_end(): void
    {
        $service = app(SubscriptionService::class);
        $sub     = Subscription::factory()->create([
            'tenant_id'           => $this->tenant->id,
            'plan_id'             => $this->plan->id,
            'provider'            => 'free',
            'provider_id'         => 'free-' . $this->tenant->id,
            'status'              => 'active',
            'current_period_end'  => now()->addMonth(),
        ]);

        $updated = $service->cancel($sub, false);

        $this->assertNotNull($updated->cancel_at);
        $this->assertEquals('active', $updated->status);
    }

    public function test_cancel_subscription_immediately(): void
    {
        $service = app(SubscriptionService::class);
        $sub     = Subscription::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'plan_id'     => $this->plan->id,
            'provider'    => 'free',
            'provider_id' => 'free-' . $this->tenant->id,
            'status'      => 'active',
        ]);

        $updated = $service->cancel($sub, true);

        $this->assertEquals('cancelled', $updated->status);
    }

    public function test_pause_and_resume_subscription(): void
    {
        $service = app(SubscriptionService::class);
        $sub     = Subscription::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'plan_id'     => $this->plan->id,
            'provider'    => 'free',
            'provider_id' => 'free-' . $this->tenant->id,
            'status'      => 'active',
        ]);

        $paused  = $service->pause($sub);
        $this->assertEquals('paused', $paused->status);
        $this->assertNotNull($paused->paused_at);

        $resumed = $service->resume($paused);
        $this->assertEquals('active', $resumed->status);
        $this->assertNull($resumed->paused_at);
    }

    public function test_process_expired_trials(): void
    {
        $service = app(SubscriptionService::class);
        Subscription::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'plan_id'       => $this->plan->id,
            'provider'      => 'free',
            'provider_id'   => 'free-1',
            'status'        => 'trialing',
            'trial_ends_at' => now()->subDay(),
        ]);

        $count = $service->processExpiredTrials();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('subscriptions', ['tenant_id' => $this->tenant->id, 'status' => 'expired']);
    }

    public function test_cannot_access_billing_unauthenticated(): void
    {
        $response = $this->get('/billing');

        $response->assertRedirect('/login');
    }
}
