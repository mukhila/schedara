<?php

namespace Tests\Feature\Media;

use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaFolderTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user   = User::factory()->create();
        $this->tenant = Tenant::factory()->create();

        TenantUser::create([
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->user->id,
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        app()->instance('current.tenant', $this->tenant);
        app()->instance('current.tenant.id', $this->tenant->id);
    }

    public function test_user_can_create_root_folder(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/media/folders', ['name' => 'Design Assets']);

        $response->assertStatus(201)
            ->assertJsonStructure(['uuid', 'name', 'path']);

        $this->assertDatabaseHas('media_folders', [
            'tenant_id' => $this->tenant->id,
            'name'      => 'Design Assets',
            'path'      => '/design-assets',
        ]);
    }

    public function test_user_can_create_nested_folder(): void
    {
        $parent = MediaFolder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Design',
            'slug'      => 'design',
            'path'      => '/design',
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/media/folders', [
                'name'      => 'Logos',
                'parent_id' => $parent->uuid,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('media_folders', [
            'tenant_id' => $this->tenant->id,
            'name'      => 'Logos',
            'parent_id' => $parent->id,
            'path'      => '/design/logos',
        ]);
    }

    public function test_folder_rename_updates_path(): void
    {
        $folder = MediaFolder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Old Name',
            'slug'      => 'old-name',
            'path'      => '/old-name',
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->putJson("/api/media/folders/{$folder->uuid}", ['name' => 'New Name']);

        $response->assertOk();

        $this->assertDatabaseHas('media_folders', [
            'id'   => $folder->id,
            'name' => 'New Name',
            'path' => '/new-name',
        ]);
    }

    public function test_folder_deletion_moves_files_to_root(): void
    {
        $folder = MediaFolder::factory()->create(['tenant_id' => $this->tenant->id, 'path' => '/test']);
        $media  = MediaLibrary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'folder_id' => $folder->id,
        ]);

        $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->deleteJson("/api/media/folders/{$folder->uuid}")
            ->assertOk();

        $this->assertSoftDeleted('media_folders', ['id' => $folder->id]);
        $this->assertDatabaseHas('media_library', ['id' => $media->id, 'folder_id' => null]);
    }

    public function test_folder_tree_returns_nested_structure(): void
    {
        $parent = MediaFolder::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Parent', 'path' => '/parent', 'slug' => 'parent']);
        MediaFolder::factory()->create(['tenant_id' => $this->tenant->id, 'parent_id' => $parent->id, 'name' => 'Child', 'path' => '/parent/child', 'slug' => 'child']);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->getJson('/api/media/folders/tree');

        $response->assertOk()
            ->assertJsonStructure([['uuid', 'name', 'children']]);
    }

    public function test_tenant_cannot_access_another_tenants_folder(): void
    {
        $otherTenant = Tenant::factory()->create();
        $folder      = MediaFolder::factory()->create(['tenant_id' => $otherTenant->id, 'path' => '/other']);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->deleteJson("/api/media/folders/{$folder->uuid}");

        $response->assertNotFound();
    }
}
