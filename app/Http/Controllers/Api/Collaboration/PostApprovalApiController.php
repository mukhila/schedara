<?php

namespace App\Http\Controllers\Api\Collaboration;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostApproval;
use App\Services\Collaboration\PostApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostApprovalApiController extends Controller
{
    public function __construct(private readonly PostApprovalService $approvals) {}

    public function request(Request $request): JsonResponse
    {
        $data = $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'comment' => 'nullable|string|max:1000',
        ]);

        $tenant = app('current.tenant');
        $post   = Post::where('tenant_id', $tenant->id)->findOrFail($data['post_id']);

        $approval = $this->approvals->request($post, $request->user()->id, $data['comment'] ?? null);

        return response()->json(['data' => $approval->load('post')], 201);
    }

    public function approve(Request $request, string $uuid): JsonResponse
    {
        $tenant   = app('current.tenant');
        $approval = PostApproval::where('tenant_id', $tenant->id)->where('uuid', $uuid)->where('status', 'pending')->firstOrFail();

        $data = $request->validate(['comment' => 'nullable|string|max:1000']);

        $this->approvals->approve($approval, $request->user()->id, $data['comment'] ?? null);

        return response()->json(['message' => 'Post approved.', 'data' => $approval->fresh()]);
    }

    public function reject(Request $request, string $uuid): JsonResponse
    {
        $tenant   = app('current.tenant');
        $approval = PostApproval::where('tenant_id', $tenant->id)->where('uuid', $uuid)->where('status', 'pending')->firstOrFail();

        $data = $request->validate(['reason' => 'required|string|max:1000']);

        $this->approvals->reject($approval, $request->user()->id, $data['reason']);

        return response()->json(['message' => 'Post rejected.', 'data' => $approval->fresh()]);
    }

    public function index(Request $request): JsonResponse
    {
        $tenant  = app('current.tenant');
        $filters = $request->only(['status', 'requested_by', 'per_page']);

        return response()->json([
            'data' => $this->approvals->allForTenant($tenant->id, $filters),
        ]);
    }

    public function pending(): JsonResponse
    {
        $tenant = app('current.tenant');
        return response()->json([
            'data' => $this->approvals->pendingForTenant($tenant->id),
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $tenant   = app('current.tenant');
        $approval = PostApproval::where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->with(['post', 'requester', 'reviewer'])
            ->firstOrFail();

        return response()->json(['data' => $approval]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(['data' => $this->approvals->stats(app('current.tenant')->id)]);
    }
}
