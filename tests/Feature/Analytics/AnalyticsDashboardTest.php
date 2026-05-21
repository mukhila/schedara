<?php

namespace Tests\Feature\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Models\AccountAnalytic;
use App\Models\PostAnalytic;
use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Analytics\AnalyticsDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
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

    public function test_overview_returns_correct_structure(): void
    {
        $service = app(AnalyticsDashboardService::class);
        $filter  = new AnalyticsFilterDTO(
            tenantId: $this->tenant->id,
            range:    DateRangeDTO::lastDays(30),
        );

        $overview = $service->overview($filter);

        $this->assertArrayHasKey('kpi', $overview);
        $this->assertArrayHasKey('followers', $overview);
        $this->assertArrayHasKey('by_platform', $overview);
        $this->assertArrayHasKey('time_series', $overview);
        $this->assertArrayHasKey('date_range', $overview);
    }

    public function test_kpi_aggregates_post_analytics(): void
    {
        $account = SocialAccount::factory()->create(['tenant_id' => $this->tenant->id]);

        PostAnalytic::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'social_account_id' => $account->id,
            'platform'        => 'instagram',
            'likes'           => 100,
            'comments'        => 20,
            'shares'          => 10,
            'reach'           => 1000,
            'impressions'     => 1500,
            'clicks'          => 50,
            'fetched_at'      => now(),
        ]);

        $service = app(AnalyticsDashboardService::class);
        $filter  = new AnalyticsFilterDTO(
            tenantId: $this->tenant->id,
            range:    DateRangeDTO::lastDays(7),
        );

        $overview = $service->overview($filter);

        $this->assertEquals(1000, $overview['kpi']['total_reach']);
        $this->assertEquals(100, $overview['kpi']['total_likes']);
    }

    public function test_follower_summary_calculates_net_growth(): void
    {
        $account = SocialAccount::factory()->create(['tenant_id' => $this->tenant->id]);

        AccountAnalytic::factory()->create([
            'tenant_id'         => $this->tenant->id,
            'social_account_id' => $account->id,
            'date'              => now()->subDays(10)->toDateString(),
            'followers'         => 1000,
            'unfollows'         => 0,
        ]);

        AccountAnalytic::factory()->create([
            'tenant_id'         => $this->tenant->id,
            'social_account_id' => $account->id,
            'date'              => now()->toDateString(),
            'followers'         => 1200,
            'unfollows'         => 50,
        ]);

        $service = app(AnalyticsDashboardService::class);
        $filter  = new AnalyticsFilterDTO(
            tenantId: $this->tenant->id,
            range:    DateRangeDTO::lastDays(30),
        );

        $overview = $service->overview($filter);
        $this->assertEquals(200, $overview['followers']['net_growth']);
    }

    public function test_tenant_isolation_in_analytics(): void
    {
        $otherTenant  = Tenant::factory()->create();
        $otherAccount = SocialAccount::factory()->create(['tenant_id' => $otherTenant->id]);

        PostAnalytic::factory()->create([
            'tenant_id'         => $otherTenant->id,
            'social_account_id' => $otherAccount->id,
            'reach'             => 99999,
            'fetched_at'        => now(),
        ]);

        $service = app(AnalyticsDashboardService::class);
        $filter  = new AnalyticsFilterDTO(
            tenantId: $this->tenant->id,
            range:    DateRangeDTO::lastDays(30),
        );

        $overview = $service->overview($filter);
        $this->assertEquals(0, $overview['kpi']['total_reach']);
    }

    public function test_dashboard_web_route_returns_200(): void
    {
        $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->get('/dashboard')
            ->assertStatus(200);
    }
}
