<?php

namespace App\Http\Controllers\Api\Collaboration;

use App\Http\Controllers\Controller;
use App\Models\CollaborationTask;
use App\Services\Collaboration\InternalCommentService;
use App\Services\Collaboration\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    public function __construct(
        private readonly TaskService            $tasks,
        private readonly InternalCommentService $comments,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filters = $request->only(['status', 'priority', 'assigned_to', 'search', 'per_page']);

        return response()->json([
            'data' => $this->tasks->forTenant($tenant->id, $filters),
        ]);
    }

    public function kanban(Request $request): JsonResponse
    {
        $tenant  = app('current.tenant');
        $filters = $request->only(['assigned_to', 'priority']);

        return response()->json([
            'data' => $this->tasks->kanbanBoard($tenant->id, $filters),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'post_id'     => 'nullable|integer|exists:posts,id',
            'due_date'    => 'nullable|date',
            'labels'      => 'nullable|array',
        ]);

        $task = $this->tasks->create($data, app('current.tenant')->id, $request->user());

        return response()->json(['data' => $task->load(['assignedBy', 'assignedTo'])], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $task   = CollaborationTask::where('tenant_id', $tenant->id)->where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'data' => $task->load(['assignedBy', 'assignedTo', 'post']),
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $task   = CollaborationTask::where('tenant_id', $tenant->id)->where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'priority'    => 'sometimes|in:low,medium,high,urgent',
            'status'      => 'sometimes|in:pending,in_progress,review,completed,rejected',
            'assigned_to' => 'sometimes|nullable|integer|exists:users,id',
            'due_date'    => 'sometimes|nullable|date',
            'labels'      => 'sometimes|nullable|array',
        ]);

        return response()->json(['data' => $this->tasks->update($task, $data)]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $task   = CollaborationTask::where('tenant_id', $tenant->id)->where('uuid', $uuid)->firstOrFail();

        $this->tasks->delete($task);

        return response()->json(['message' => 'Task deleted.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status'     => 'required|in:pending,in_progress,review,completed,rejected',
            'task_uuids' => 'required|array',
        ]);

        $this->tasks->reorder(app('current.tenant')->id, $data['status'], $data['task_uuids']);

        return response()->json(['message' => 'Order updated.']);
    }

    public function stats(): JsonResponse
    {
        return response()->json(['data' => $this->tasks->stats(app('current.tenant')->id)]);
    }

    public function comments(string $uuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $task   = CollaborationTask::where('tenant_id', $tenant->id)->where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'data' => $this->comments->forTask($task->id, $tenant->id),
        ]);
    }
}
