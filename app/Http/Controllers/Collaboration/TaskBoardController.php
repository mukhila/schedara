<?php

namespace App\Http\Controllers\Collaboration;

use App\Http\Controllers\Controller;
use App\Models\CollaborationTask;
use App\Models\TenantUser;
use App\Services\Collaboration\InternalCommentService;
use App\Services\Collaboration\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskBoardController extends Controller
{
    public function __construct(
        private readonly TaskService            $tasks,
        private readonly InternalCommentService $comments,
    ) {}

    public function index(Request $request): View
    {
        $tenant  = app('current.tenant');
        $filters = $request->only(['assigned_to', 'priority']);

        $board = $this->tasks->kanbanBoard($tenant->id, $filters);

        $members = TenantUser::where('tenant_id', $tenant->id)
            ->whereNotNull('joined_at')
            ->with('user')
            ->get();

        return view('backend.collaboration.tasks.index', compact('board', 'members', 'filters'));
    }

    public function show(string $uuid): View
    {
        $tenant = app('current.tenant');
        $task   = CollaborationTask::where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->with(['assignedBy', 'assignedTo', 'post'])
            ->firstOrFail();

        $threadedComments = $this->comments->forTask($task->id, $tenant->id);

        $members = TenantUser::where('tenant_id', $tenant->id)
            ->whereNotNull('joined_at')
            ->with('user')
            ->get();

        return view('backend.collaboration.tasks.show', compact('task', 'threadedComments', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $this->tasks->create($data, app('current.tenant')->id, $request->user());

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, string $uuid): RedirectResponse
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
        ]);

        $this->tasks->update($task, $data);

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Request $request, string $uuid): RedirectResponse
    {
        $tenant = app('current.tenant');
        $task   = CollaborationTask::where('tenant_id', $tenant->id)->where('uuid', $uuid)->firstOrFail();

        $this->tasks->delete($task);

        return redirect()->route('collaboration.tasks.index')->with('success', 'Task deleted.');
    }
}
