<?php

namespace Tests\Feature\Analytics;

use App\Models\AnalyticsCampaign;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Analytics\CampaignAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User   $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create();
        $this->tenant->users()->attach($this->user, ['role' => 'owner', 'joined_at' => now()]);
    }

    public function test_create_campaign(): void
    {
        $service = app(CampaignAnalyticsService::class);
        $campaign = $service->create($this->tenant->id, $this->user->id, [
            'name'       => 'Test Campaign',
            'platform'   => 'instagram',
            'status'     => 'active',
            'start_date' => now()->toDateString(),
            'budget'     => 1000,
        ]);

        $this->assertDatabaseHas('analytics_campaigns', [
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Campaign',
            'platform'  => 'instagram',
        ]);
        $this->assertNotNull($campaign->uuid);
    }

    public function test_update_metrics_computes_roi(): void
    {
        $service  = app(CampaignAnalyticsService::class);
        $campaign = AnalyticsCampaign::create([
            'tenant_id'  => $this->tenant->id,
            'created_by' => $this->user->id,
            'name'       => 'ROI Test',
            'status'     => 'active',
            'start_date' => now()->toDateString(),
        ]);

        $updated = $service->updateMetrics($campaign, [
            'spend'   => 1000,
            'revenue' => 3000,
            'clicks'  => 200,
            'impressions' => 5000,
        ]);

        $this->assertEquals(200.0, $updated->roi);  // (3000-1000)/1000*100
        $this->assertEquals(3.0, $updated->roas);   // 3000/1000
    }

    public function test_campaign_api_requires_auth(): void
    {
        $this->getJson('/api/analytics/campaigns')
            ->assertStatus(401);
    }

    public function test_top_performers_returns_by_roi(): void
    {
        AnalyticsCampaign::create([
            'tenant_id' => $this->tenant->id, 'created_by' => $this->user->id,
            'name' => 'High ROI', 'status' => 'completed', 'start_date' => now()->subMonth()->toDateString(),
            'roi' => 300, 'spend' => 1000, 'revenue' => 4000,
        ]);
        AnalyticsCampaign::create([
            'tenant_id' => $this->tenant->id, 'created_by' => $this->user->id,
            'name' => 'Low ROI', 'status' => 'completed', 'start_date' => now()->subMonth()->toDateString(),
            'roi' => 50, 'spend' => 2000, 'revenue' => 3000,
        ]);

        $service = app(CampaignAnalyticsService::class);
        $top     = $service->topPerformers($this->tenant->id, 5);

        $this->assertEquals('High ROI', $top[0]['name']);
    }

    public function test_tenant_isolation_for_campaigns(): void
    {
        $other = Tenant::factory()->create();
        $otherUser = User::factory()->create();
        AnalyticsCampaign::create([
            'tenant_id' => $other->id, 'created_by' => $otherUser->id,
            'name' => 'Other Tenant Campaign', 'status' => 'active', 'start_date' => now()->toDateString(),
        ]);

        $count = AnalyticsCampaign::forTenant($this->tenant->id)->count();
        $this->assertEquals(0, $count);
    }
}
