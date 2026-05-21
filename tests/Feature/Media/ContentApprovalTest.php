<?php

namespace Tests\Feature\Media;

use App\Models\ContentApproval;
use App\Models\MediaLibrary;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ContentApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User   $owner;
    private User   $reviewer;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner    = User::factory()->create();
        $this->reviewer = User::factory()->create();
        $this->tenant   = Tenant::factory()->create();

        foreach ([$this->owner, $this->reviewer] as $user) {
            TenantUser::create([
                'tenant_id' => $this->tenant->id,
                'user_id'   => $user->id,
                'role'      => 'owner',
                'joined_at' => now(),
            ]);
        }

        app()->instance('current.tenant', $this->tenant);
        app()->instance('current.tenant.id', $this->tenant->id);
    }

    public function test_user_can_request_approval(): void
    {
        $media = MediaLibrary::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($this->owner)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson("/api/media/{$media->uuid}/request-approval");

        $response->assertOk();

        $this->assertDatabaseHas('media_library', [
            'id'              => $media->id,
            'approval_status' => 'pending',
        ]);

        $this->assertDatabaseHas('content_approvals', [
            'media_file_id' => $media->id,
            'status'        => 'pending',
        ]);
    }

    public function test_reviewer_can_approve_pending_media(): void
    {
        $media    = MediaLibrary::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'approval_status' => 'pending',
        ]);
        $approval = ContentApproval::create([
            'media_file_id' => $media->id,
            'requested_by'  => $this->owner->id,
            'status'        => 'pending',
        ]);

        Event::fake();

        $response = $this->actingAs($this->reviewer)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson("/api/media/{$media->uuid}/approve", ['comments' => 'Looks great!']);

        $response->assertOk();

        $this->assertDatabaseHas('content_approvals', [
            'id'         => $approval->id,
            'status'     => 'approved',
            'approved_by'=> $this->reviewer->id,
            'comments'   => 'Looks great!',
        ]);

        $this->assertDatabaseHas('media_library', [
            'id'              => $media->id,
            'approval_status' => 'approved',
        ]);

        Event::assertDispatched(\App\Events\Media\ContentApproved::class);
    }

    public function test_reviewer_can_reject_pending_media(): void
    {
        $media    = MediaLibrary::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'approval_status' => 'pending',
        ]);
        ContentApproval::create([
            'media_file_id' => $media->id,
            'requested_by'  => $this->owner->id,
            'status'        => 'pending',
        ]);

        Event::fake();

        $response = $this->actingAs($this->reviewer)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson("/api/media/{$media->uuid}/reject", ['comments' => 'Needs revision.']);

        $response->assertOk();

        $this->assertDatabaseHas('media_library', [
            'id'              => $media->id,
            'approval_status' => 'rejected',
        ]);

        Event::assertDispatched(\App\Events\Media\ContentRejected::class);
    }

    public function test_rejection_requires_comments(): void
    {
        $media = MediaLibrary::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'approval_status' => 'pending',
        ]);
        ContentApproval::create([
            'media_file_id' => $media->id,
            'requested_by'  => $this->owner->id,
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($this->reviewer)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson("/api/media/{$media->uuid}/reject", []);

        $response->assertUnprocessable();
    }

    public function test_bulk_approve_approves_pending_files(): void
    {
        $media1 = MediaLibrary::factory()->create(['tenant_id' => $this->tenant->id, 'approval_status' => 'pending']);
        $media2 = MediaLibrary::factory()->create(['tenant_id' => $this->tenant->id, 'approval_status' => 'pending']);

        foreach ([$media1, $media2] as $m) {
            ContentApproval::create(['media_file_id' => $m->id, 'requested_by' => $this->owner->id, 'status' => 'pending']);
        }

        $response = $this->actingAs($this->reviewer)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/media/bulk/approve', [
                'uuids'    => [$media1->uuid, $media2->uuid],
                'comments' => 'Bulk approved',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('media_library', ['id' => $media1->id, 'approval_status' => 'approved']);
        $this->assertDatabaseHas('media_library', ['id' => $media2->id, 'approval_status' => 'approved']);
    }
}
