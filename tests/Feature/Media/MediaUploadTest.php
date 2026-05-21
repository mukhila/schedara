<?php

namespace Tests\Feature\Media;

use App\Models\MediaLibrary;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Queue::fake();

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

    public function test_authenticated_user_can_upload_image(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 400, 300);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/media/upload', [
                'files' => [$file],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => [['uuid', 'name', 'type', 'size', 'url']]]);

        $this->assertDatabaseHas('media_library', [
            'tenant_id' => $this->tenant->id,
            'type'      => 'image',
            'extension' => 'jpg',
        ]);
    }

    public function test_upload_stores_file_hash_for_duplicate_detection(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/media/upload', ['files' => [$file]]);

        $media = MediaLibrary::where('tenant_id', $this->tenant->id)->first();

        $this->assertNotNull($media->file_hash);
        $this->assertEquals(64, strlen($media->file_hash)); // SHA-256
    }

    public function test_upload_queues_optimization_job_for_images(): void
    {
        $file = UploadedFile::fake()->image('banner.png');

        $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/media/upload', ['files' => [$file]]);

        Queue::assertPushed(\App\Jobs\Media\OptimizeImageJob::class);
    }

    public function test_upload_rejects_invalid_file_type(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/media/upload', ['files' => [$file]]);

        $response->assertStatus(422);
    }

    public function test_upload_with_tags_syncs_tags(): void
    {
        $file = UploadedFile::fake()->image('design.png');

        $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/media/upload', [
                'files' => [$file],
                'tags'  => ['design', 'marketing'],
            ]);

        $this->assertDatabaseHas('media_tags', ['tenant_id' => $this->tenant->id, 'slug' => 'design']);
        $this->assertDatabaseHas('media_tags', ['tenant_id' => $this->tenant->id, 'slug' => 'marketing']);
    }

    public function test_guest_cannot_upload(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $this->postJson('/api/media/upload', ['files' => [$file]])
             ->assertUnauthorized();
    }

    public function test_media_index_returns_paginated_results(): void
    {
        MediaLibrary::factory()->count(5)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->getJson('/api/media');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['total', 'per_page', 'current_page']]);
    }

    public function test_media_can_be_deleted(): void
    {
        $media = MediaLibrary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'disk'      => 'local',
            's3_key'    => 'uploads/images/test.jpg',
        ]);

        Storage::fake('local')->put('uploads/images/test.jpg', 'fake-content');

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->deleteJson("/api/media/{$media->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('media_library', ['id' => $media->id]);
    }

    public function test_toggle_favorite(): void
    {
        $media = MediaLibrary::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'is_favorite' => false,
        ]);

        $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson("/api/media/{$media->uuid}/favorite");

        $this->assertDatabaseHas('media_library', ['id' => $media->id, 'is_favorite' => true]);
    }
}
