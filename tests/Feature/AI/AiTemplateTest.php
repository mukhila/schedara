<?php

namespace Tests\Feature\AI;

use App\Models\AiTemplate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create();
        $this->user->tenants()->attach($this->tenant->id, ['role' => 'owner', 'joined_at' => now()]);

        $this->actingAs($this->user, 'sanctum');
        $this->withHeader('X-Tenant-ID', $this->tenant->uuid);
    }

    public function test_index_returns_tenant_templates(): void
    {
        AiTemplate::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);
        AiTemplate::factory()->count(2)->create(); // other tenant

        $this->getJson('/api/ai/assistant/templates')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_template(): void
    {
        $this->postJson('/api/ai/assistant/templates', [
            'name' => 'My template',
            'type' => 'caption',
            'body' => 'Write a caption for {product} in a {tone} tone.',
        ])->assertCreated()
          ->assertJsonPath('data.name', 'My template');
    }

    public function test_can_update_own_template(): void
    {
        $template = AiTemplate::factory()->create(['tenant_id' => $this->tenant->id, 'is_system' => false]);

        $this->putJson("/api/ai/assistant/templates/{$template->id}", [
            'name' => 'Updated name',
            'type' => 'caption',
            'body' => 'New body {topic}',
        ])->assertOk()
          ->assertJsonPath('data.name', 'Updated name');
    }

    public function test_cannot_delete_system_template(): void
    {
        $template = AiTemplate::factory()->create(['tenant_id' => $this->tenant->id, 'is_system' => true]);

        $this->deleteJson("/api/ai/assistant/templates/{$template->id}")
            ->assertForbidden();
    }

    public function test_can_delete_own_template(): void
    {
        $template = AiTemplate::factory()->create(['tenant_id' => $this->tenant->id, 'is_system' => false]);

        $this->deleteJson("/api/ai/assistant/templates/{$template->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('ai_templates', ['id' => $template->id]);
    }

    public function test_cannot_access_other_tenant_template(): void
    {
        $other = AiTemplate::factory()->create(); // different tenant

        $this->putJson("/api/ai/assistant/templates/{$other->id}", [
            'name' => 'Hacked',
            'type' => 'caption',
            'body' => 'x',
        ])->assertNotFound();
    }
}
