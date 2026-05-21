<?php

namespace Tests\Feature\Collaboration;

use App\Enums\TaskStatus;
use App\Models\CollaborationTask;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private User   $assignee;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant  = Tenant::factory()->create();
        $this->user    = User::factory()->create();
        $this->assignee= User::factory()->create();

        foreach ([$this->user, $this->assignee] as $u) {
            $u->tenants()->attach($this->tenant->id, ['role' => 'manager', 'joined_at' => now()]);
        }

        $this->actingAs($this->user, 'sanctum');
        $this->withHeader('X-Tenant-ID', $this->tenant->uuid);
    }

    public function test_can_create_task(): void
    {
        $response = $this->postJson('/api/collaboration/tasks', [
            'title'       => 'Write blog post',
            'priority'    => 'high',
            'assigned_to' => $this->assignee->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Write blog post')
            ->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('collaboration_tasks', [
            'tenant_id'   => $this->tenant->id,
            'title'       => 'Write blog post',
            'assigned_to' => $this->assignee->id,
        ]);
    }

    public function test_can_list_tasks(): void
    {
        CollaborationTask::create([
            'uuid'        => \Illuminate\Support\Str::uuid(),
            'tenant_id'   => $this->tenant->id,
            'assigned_by' => $this->user->id,
            'title'       => 'Test task',
            'priority'    => 'medium',
            'status'      => 'pending',
        ]);

        $this->getJson('/api/collaboration/tasks')
            ->assertOk()
            ->assertJsonStructure(['data' => ['data']]);
    }

    public function test_can_update_task_status(): void
    {
        $task = CollaborationTask::create([
            'uuid'        => \Illuminate\Support\Str::uuid(),
            'tenant_id'   => $this->tenant->id,
            'assigned_by' => $this->user->id,
            'title'       => 'Design mockup',
            'priority'    => 'medium',
            'status'      => 'pending',
        ]);

        $this->putJson("/api/collaboration/tasks/{$task->uuid}", ['status' => 'in_progress'])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');
    }

    public function test_can_get_kanban_board(): void
    {
        $this->getJson('/api/collaboration/tasks/kanban')
            ->assertOk()
            ->assertJsonStructure(['data' => ['pending', 'in_progress', 'review', 'completed', 'rejected']]);
    }

    public function test_can_delete_task(): void
    {
        $task = CollaborationTask::create([
            'uuid'        => \Illuminate\Support\Str::uuid(),
            'tenant_id'   => $this->tenant->id,
            'assigned_by' => $this->user->id,
            'title'       => 'Delete me',
            'priority'    => 'low',
            'status'      => 'pending',
        ]);

        $this->deleteJson("/api/collaboration/tasks/{$task->uuid}")->assertOk();
        $this->assertSoftDeleted('collaboration_tasks', ['id' => $task->id]);
    }

    public function test_task_creation_fires_event_when_assigned(): void
    {
        \Illuminate\Support\Facades\Event::fake();

        $this->postJson('/api/collaboration/tasks', [
            'title'       => 'Assigned task',
            'priority'    => 'medium',
            'assigned_to' => $this->assignee->id,
        ]);

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\Collaboration\TaskAssigned::class);
    }

    public function test_cannot_access_tasks_from_another_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherTask   = CollaborationTask::create([
            'uuid'        => \Illuminate\Support\Str::uuid(),
            'tenant_id'   => $otherTenant->id,
            'assigned_by' => $this->user->id,
            'title'       => 'Another tenant task',
            'priority'    => 'low',
            'status'      => 'pending',
        ]);

        $this->getJson("/api/collaboration/tasks/{$otherTask->uuid}")->assertNotFound();
    }

    public function test_unauthenticated_cannot_access_tasks(): void
    {
        $this->withoutMiddleware(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
        $this->postJson('/api/collaboration/tasks', ['title' => 'x'])->assertUnauthorized();
    }
}
