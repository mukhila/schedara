<?php

namespace App\Services\Collaboration;

use App\Enums\TaskStatus;
use App\Events\Collaboration\TaskAssigned;
use App\Events\Collaboration\TaskCompleted;
use App\Models\ActivityLog;
use App\Models\CollaborationTask;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TaskService
{
    public function create(array $data, int $tenantId, User $creator): CollaborationTask
    {
        $task = CollaborationTask::create([
            'uuid'        => Str::uuid(),
            'tenant_id'   => $tenantId,
            'assigned_by' => $creator->id,
            'assigned_to' => $data['assigned_to'] ?? null,
            'post_id'     => $data['post_id'] ?? null,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'priority'    => $data['priority'] ?? 'medium',
            'status'      => TaskStatus::Pending->value,
            'due_date'    => $data['due_date'] ?? null,
            'labels'      => $data['labels'] ?? null,
        ]);

        ActivityLog::record('task_created', 'tasks', "Task \"{$task->title}\" created", ['task_uuid' => $task->uuid], $tenantId);

        if ($task->assigned_to && $task->assigned_to !== $creator->id) {
            event(new TaskAssigned($task, $creator));
        }

        return $task;
    }

    public function update(CollaborationTask $task, array $data): CollaborationTask
    {
        $wasAssignedTo = $task->assigned_to;

        $task->update(array_filter([
            'title'       => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'priority'    => $data['priority'] ?? null,
            'status'      => $data['status'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date'    => $data['due_date'] ?? null,
            'labels'      => $data['labels'] ?? null,
        ], fn ($v) => $v !== null));

        // If assignment changed, fire event
        $newAssignee = $data['assigned_to'] ?? null;
        if ($newAssignee && $newAssignee !== $wasAssignedTo) {
            event(new TaskAssigned($task->fresh(), auth()->user()));
        }

        // If marked completed, fire event
        if (isset($data['status']) && $data['status'] === TaskStatus::Completed->value && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
            event(new TaskCompleted($task));
        }

        ActivityLog::record('task_updated', 'tasks', "Task \"{$task->title}\" updated", ['task_uuid' => $task->uuid]);

        return $task->fresh();
    }

    public function updateStatus(CollaborationTask $task, string $status): CollaborationTask
    {
        return $this->update($task, ['status' => $status]);
    }

    public function delete(CollaborationTask $task): void
    {
        ActivityLog::record('task_deleted', 'tasks', "Task \"{$task->title}\" deleted", ['task_uuid' => $task->uuid]);
        $task->delete();
    }

    public function forTenant(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        return CollaborationTask::where('tenant_id', $tenantId)
            ->when(isset($filters['status']),      fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['priority']),    fn ($q) => $q->where('priority', $filters['priority']))
            ->when(isset($filters['assigned_to']), fn ($q) => $q->where('assigned_to', $filters['assigned_to']))
            ->when(isset($filters['search']),      fn ($q) => $q->where('title', 'like', "%{$filters['search']}%"))
            ->with(['assignedBy', 'assignedTo'])
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->orderBy('due_date')
            ->paginate($filters['per_page'] ?? 25);
    }

    public function kanbanBoard(int $tenantId, array $filters = []): array
    {
        $tasks = CollaborationTask::where('tenant_id', $tenantId)
            ->when(isset($filters['assigned_to']), fn ($q) => $q->where('assigned_to', $filters['assigned_to']))
            ->when(isset($filters['priority']),    fn ($q) => $q->where('priority', $filters['priority']))
            ->with(['assignedTo'])
            ->orderBy('sort_order')
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->get();

        return collect(TaskStatus::kanbanOrder())
            ->mapWithKeys(fn (TaskStatus $status) => [
                $status->value => $tasks->where('status', $status->value)->values(),
            ])
            ->all();
    }

    public function reorder(int $tenantId, string $status, array $taskUuids): void
    {
        foreach ($taskUuids as $i => $uuid) {
            CollaborationTask::where('tenant_id', $tenantId)
                ->where('uuid', $uuid)
                ->update(['sort_order' => $i, 'status' => $status]);
        }
    }

    public function stats(int $tenantId): array
    {
        $base = CollaborationTask::where('tenant_id', $tenantId);

        return [
            'total'       => (clone $base)->count(),
            'pending'     => (clone $base)->where('status', 'pending')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'review'      => (clone $base)->where('status', 'review')->count(),
            'completed'   => (clone $base)->where('status', 'completed')->count(),
            'overdue'     => (clone $base)->overdue()->count(),
        ];
    }
}
