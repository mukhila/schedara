<?php

namespace Tests\Feature\Collaboration;

use App\Models\Post;
use App\Models\PostApproval;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User   $creator;
    private User   $reviewer;
    private Tenant $tenant;
    private Post   $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant   = Tenant::factory()->create();
        $this->creator  = User::factory()->create();
        $this->reviewer = User::factory()->create();

        $this->creator->tenants()->attach($this->tenant->id,  ['role' => 'creator', 'joined_at' => now()]);
        $this->reviewer->tenants()->attach($this->tenant->id, ['role' => 'manager', 'joined_at' => now()]);

        $this->post = Post::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->creator->id,
            'status'    => Post::STATUS_DRAFT,
        ]);

        $this->actingAs($this->creator, 'sanctum');
        $this->withHeader('X-Tenant-ID', $this->tenant->uuid);
    }

    public function test_creator_can_request_approval(): void
    {
        $response = $this->postJson('/api/collaboration/approvals/request', [
            'post_id' => $this->post->id,
            'comment' => 'Please review this post.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('post_approvals', [
            'post_id'      => $this->post->id,
            'requested_by' => $this->creator->id,
            'status'       => 'pending',
        ]);

        $this->post->refresh();
        $this->assertEquals(Post::STATUS_PENDING_APPROVAL, $this->post->status);
    }

    public function test_reviewer_can_approve_post(): void
    {
        $approval = PostApproval::create([
            'uuid'         => \Illuminate\Support\Str::uuid(),
            'tenant_id'    => $this->tenant->id,
            'post_id'      => $this->post->id,
            'requested_by' => $this->creator->id,
            'status'       => 'pending',
        ]);

        $this->actingAs($this->reviewer, 'sanctum');

        $this->postJson("/api/collaboration/approvals/{$approval->uuid}/approve", [
            'comment' => 'Looks great!',
        ])->assertOk();

        $this->assertDatabaseHas('post_approvals', [
            'id'          => $approval->id,
            'status'      => 'approved',
            'approved_by' => $this->reviewer->id,
        ]);

        $this->post->refresh();
        $this->assertEquals(Post::STATUS_APPROVED, $this->post->status);
    }

    public function test_reviewer_can_reject_post_with_reason(): void
    {
        $approval = PostApproval::create([
            'uuid'         => \Illuminate\Support\Str::uuid(),
            'tenant_id'    => $this->tenant->id,
            'post_id'      => $this->post->id,
            'requested_by' => $this->creator->id,
            'status'       => 'pending',
        ]);

        $this->actingAs($this->reviewer, 'sanctum');

        $this->postJson("/api/collaboration/approvals/{$approval->uuid}/reject", [
            'reason' => 'Needs more detail in the caption.',
        ])->assertOk();

        $this->assertDatabaseHas('post_approvals', [
            'id'     => $approval->id,
            'status' => 'rejected',
        ]);
    }

    public function test_reject_requires_reason(): void
    {
        $approval = PostApproval::create([
            'uuid'         => \Illuminate\Support\Str::uuid(),
            'tenant_id'    => $this->tenant->id,
            'post_id'      => $this->post->id,
            'requested_by' => $this->creator->id,
            'status'       => 'pending',
        ]);

        $this->actingAs($this->reviewer, 'sanctum');

        $this->postJson("/api/collaboration/approvals/{$approval->uuid}/reject", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_approval_events_are_fired(): void
    {
        \Illuminate\Support\Facades\Event::fake();

        $approval = PostApproval::create([
            'uuid'         => \Illuminate\Support\Str::uuid(),
            'tenant_id'    => $this->tenant->id,
            'post_id'      => $this->post->id,
            'requested_by' => $this->creator->id,
            'status'       => 'pending',
        ]);

        $this->actingAs($this->reviewer, 'sanctum');
        $this->postJson("/api/collaboration/approvals/{$approval->uuid}/approve");

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\Collaboration\PostApproved::class);
    }

    public function test_cannot_approve_already_reviewed_approval(): void
    {
        $approval = PostApproval::create([
            'uuid'         => \Illuminate\Support\Str::uuid(),
            'tenant_id'    => $this->tenant->id,
            'post_id'      => $this->post->id,
            'requested_by' => $this->creator->id,
            'status'       => 'approved',
        ]);

        $this->actingAs($this->reviewer, 'sanctum');

        $this->postJson("/api/collaboration/approvals/{$approval->uuid}/approve")
            ->assertNotFound();
    }

    public function test_pending_approvals_list_is_scoped_to_tenant(): void
    {
        PostApproval::create([
            'uuid'         => \Illuminate\Support\Str::uuid(),
            'tenant_id'    => $this->tenant->id,
            'post_id'      => $this->post->id,
            'requested_by' => $this->creator->id,
            'status'       => 'pending',
        ]);

        $this->actingAs($this->reviewer, 'sanctum');

        $this->getJson('/api/collaboration/approvals/pending')
            ->assertOk()
            ->assertJsonStructure(['data' => ['data']]);
    }
}
