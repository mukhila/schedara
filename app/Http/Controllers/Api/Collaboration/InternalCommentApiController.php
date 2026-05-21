<?php

namespace App\Http\Controllers\Api\Collaboration;

use App\Http\Controllers\Controller;
use App\Models\InternalComment;
use App\Services\Collaboration\InternalCommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalCommentApiController extends Controller
{
    public function __construct(private readonly InternalCommentService $comments) {}

    public function storeForPost(Request $request, int $postId): JsonResponse
    {
        return $this->store($request, ['post_id' => $postId]);
    }

    public function storeForTask(Request $request, string $taskUuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $task   = \App\Models\CollaborationTask::where('tenant_id', $tenant->id)->where('uuid', $taskUuid)->firstOrFail();
        return $this->store($request, ['task_id' => $task->id]);
    }

    private function store(Request $request, array $extra): JsonResponse
    {
        $data = $request->validate([
            'comment'   => 'required|string|max:5000',
            'parent_id' => 'nullable|integer|exists:internal_comments,id',
        ]);

        $tenant  = app('current.tenant');
        $comment = $this->comments->add(array_merge($data, $extra), $tenant->id, $request->user()->id);

        return response()->json(['data' => $comment], 201);
    }

    public function forPost(int $postId): JsonResponse
    {
        $tenant = app('current.tenant');
        return response()->json([
            'data' => $this->comments->forPost($postId, $tenant->id),
        ]);
    }

    public function forTask(string $taskUuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $task   = \App\Models\CollaborationTask::where('tenant_id', $tenant->id)->where('uuid', $taskUuid)->firstOrFail();
        return response()->json([
            'data' => $this->comments->forTask($task->id, $tenant->id),
        ]);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $tenant  = app('current.tenant');
        $comment = InternalComment::where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate(['comment' => 'required|string|max:5000']);
        return response()->json(['data' => $this->comments->update($comment, $data['comment'])]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $tenant  = app('current.tenant');
        $comment = InternalComment::where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->comments->delete($comment);
        return response()->json(['message' => 'Comment deleted.']);
    }

    public function react(Request $request, string $uuid): JsonResponse
    {
        $data    = $request->validate(['emoji' => 'required|string|max:10']);
        $tenant  = app('current.tenant');
        $comment = InternalComment::where('tenant_id', $tenant->id)->where('uuid', $uuid)->firstOrFail();

        $reactions = $this->comments->react($comment, $request->user()->id, $data['emoji']);
        return response()->json(['data' => ['reactions' => $reactions]]);
    }
}
