<?php

namespace Tests\Feature\Dashboard;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetApiTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create();
        $this->user->tenants()->attach($this->tenant->id, ['role' => 'owner']);

        $this->actingAs($this->user, 'sanctum');
        $this->withHeader('X-Tenant-ID', (string) $this->tenant->id);
    }

    // ── Engagement ─────────────────────────────────────────────────────

    public function test_engagement_widget_returns_200(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/engagement');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    public function test_engagement_widget_accepts_date_params(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/engagement?from=2025-01-01&to=2025-01-31');
        $response->assertStatus(200);
    }

    // ── Followers ──────────────────────────────────────────────────────

    public function test_followers_widget_returns_200(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/followers');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    // ── Post Performance ───────────────────────────────────────────────

    public function test_post_performance_widget_returns_200(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/post-performance');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    public function test_post_performance_widget_accepts_sort_by(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/post-performance?sort_by=reach');
        $response->assertStatus(200);
    }

    // ── Platform Comparison ────────────────────────────────────────────

    public function test_platform_comparison_widget_returns_200(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/platform-comparison');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    // ── Revenue ────────────────────────────────────────────────────────

    public function test_revenue_widget_returns_200(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/revenue');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    // ── AI Insights ────────────────────────────────────────────────────

    public function test_ai_insights_widget_returns_200(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/ai-insights');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    // ── Auth guard ─────────────────────────────────────────────────────

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/dashboard/widgets/engagement')
             ->assertStatus(401);
    }

    // ── Meta structure ─────────────────────────────────────────────────

    public function test_meta_block_has_expected_keys(): void
    {
        $response = $this->getJson('/api/dashboard/widgets/engagement');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'meta' => ['from', 'to', 'days', 'generated_at'],
                 ]);
    }
}
