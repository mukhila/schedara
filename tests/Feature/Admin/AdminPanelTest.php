<?php

namespace Tests\Feature\Admin;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['is_super_admin' => true]);
    }

    private function regularUser(): User
    {
        return User::factory()->create(['is_super_admin' => false]);
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->regularUser())
             ->get('/admin')
             ->assertForbidden();
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->superAdmin())
             ->get('/admin')
             ->assertOk();
    }

    public function test_admin_can_view_users_list(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->superAdmin())
             ->get('/admin/users')
             ->assertOk()
             ->assertSee('User Management');
    }

    public function test_admin_can_suspend_user(): void
    {
        $admin = $this->superAdmin();
        $target = $this->regularUser();

        $this->actingAs($admin)
             ->post("/admin/users/{$target->id}/suspend", ['reason' => 'Test suspension'])
             ->assertRedirect();

        $this->assertNotNull($target->fresh()->suspended_at);
    }

    public function test_admin_can_activate_suspended_user(): void
    {
        $admin = $this->superAdmin();
        $target = $this->regularUser();
        $target->update(['suspended_at' => now()]);

        $this->actingAs($admin)
             ->post("/admin/users/{$target->id}/activate")
             ->assertRedirect();

        $this->assertNull($target->fresh()->suspended_at);
    }

    public function test_admin_can_view_tickets(): void
    {
        SupportTicket::factory()->count(3)->create();

        $this->actingAs($this->superAdmin())
             ->get('/admin/tickets')
             ->assertOk()
             ->assertSee('Support Tickets');
    }

    public function test_admin_cannot_revoke_own_admin(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
             ->post("/admin/users/{$admin->id}/revoke-admin")
             ->assertRedirect()
             ->assertSessionHas('error');
    }

    public function test_admin_api_returns_platform_stats(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
             ->getJson('/api/admin/stats')
             ->assertOk()
             ->assertJsonStructure(['total_users', 'active_tenants', 'mrr', 'open_tickets']);
    }

    public function test_guest_cannot_access_admin_api(): void
    {
        $this->getJson('/api/admin/stats')
             ->assertUnauthorized();
    }
}
