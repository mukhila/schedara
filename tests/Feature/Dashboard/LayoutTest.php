<?php

namespace Tests\Feature\Dashboard;

use App\Models\DashboardLayout;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
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

    // ── Show ───────────────────────────────────────────────────────────

    public function test_show_returns_layout_for_authenticated_user(): void
    {
        $response = $this->getJson('/api/dashboard/layout');
        $response->assertStatus(200)
                 ->assertJsonStructure(['order', 'hidden']);
    }

    public function test_show_creates_default_layout_if_none_exists(): void
    {
        $this->assertDatabaseMissing('dashboard_layouts', [
            'user_id'   => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->getJson('/api/dashboard/layout')->assertStatus(200);

        $this->assertDatabaseHas('dashboard_layouts', [
            'user_id'   => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────────

    public function test_update_saves_order_and_hidden(): void
    {
        $payload = [
            'order'  => ['engagement', 'kpi-cards', 'followers', 'post-performance', 'platform-comparison', 'revenue', 'ai-insights'],
            'hidden' => ['ai-insights'],
        ];

        $response = $this->putJson('/api/dashboard/layout', $payload);
        $response->assertStatus(200)
                 ->assertJsonPath('order.0', 'engagement')
                 ->assertJsonPath('hidden.0', 'ai-insights');
    }

    public function test_update_rejects_invalid_widget_keys(): void
    {
        $response = $this->putJson('/api/dashboard/layout', [
            'order'  => ['fake-widget', 'kpi-cards'],
            'hidden' => [],
        ]);

        // Service strips unknown keys; response should still be 200 with valid keys only
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotContains('fake-widget', $data['order']);
    }

    public function test_update_validates_input_types(): void
    {
        $this->putJson('/api/dashboard/layout', [
            'order'  => 'not-an-array',
            'hidden' => [],
        ])->assertStatus(422);
    }

    // ── Reset ──────────────────────────────────────────────────────────

    public function test_reset_restores_default_order(): void
    {
        // First save a custom layout
        $this->putJson('/api/dashboard/layout', [
            'order'  => ['revenue', 'kpi-cards'],
            'hidden' => ['followers'],
        ]);

        // Reset
        $response = $this->postJson('/api/dashboard/layout/reset');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertSame(DashboardLayout::defaultOrder(), $data['order']);
        $this->assertSame([], $data['hidden']);
    }

    // ── Auth guard ─────────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_layout(): void
    {
        $this->getJson('/api/dashboard/layout')->assertStatus(401);
    }

    // ── Dashboard page ─────────────────────────────────────────────────

    public function test_dashboard_page_renders_for_tenant_user(): void
    {
        // Wire up the tenant resolver that the web middleware expects
        $this->app->instance('current.tenant', $this->tenant);

        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
    }
}
