<?php

namespace App\Services\Collaboration;

use App\Events\Collaboration\PostApprovalRequested;
use App\Events\Collaboration\PostApproved;
use App\Events\Collaboration\PostRejected;
use App\Models\ActivityLog;
use App\Models\ApprovalWorkflow;
use App\Models\Post;
use App\Models\PostApproval;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PostApprovalService
{
    public function request(Post $post, int $requesterId, ?string $comment = null): PostApproval
    {
        // Cancel any existing pending approval for this post
        PostApproval::where('post_id', $post->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $approval = PostApproval::create([
            'uuid'            => Str::uuid(),
            'tenant_id'       => $post->tenant_id,
            'post_id'         => $post->id,
            'requested_by'    => $requesterId,
            'status'          => 'pending',
            'request_comment' => $comment,
        ]);

        $post->update(['status' => Post::STATUS_PENDING_APPROVAL]);

        // If an ApprovalWorkflow is configured for this tenant, start it
        $this->startWorkflowIfConfigured($post, $requesterId, $comment);

        ActivityLog::record('approval_requested', 'approvals', "Post \"{$post->title}\" submitted for approval", ['approval_uuid' => $approval->uuid], $post->tenant_id);

        event(new PostApprovalRequested($approval, $post));

        return $approval;
    }

    public function approve(PostApproval $approval, int $reviewerId, ?string $comment = null): PostApproval
    {
        // If a workflow exists for this post, advance through it
        $workflow = ApprovalWorkflow::where('post_id', $approval->post_id)
            ->whereIn('status', ['pending', 'in_review'])
            ->first();

        if ($workflow) {
            $workflow->approveCurrentStage($reviewerId, $comment);

            // Workflow handles post status internally; only fire event if fully approved
            if ($workflow->isComplete()) {
                $approval->approve($reviewerId, $comment);
                ActivityLog::record('post_approved', 'approvals', 'Post approved (all workflow stages)', ['approval_uuid' => $approval->uuid], $approval->tenant_id, $reviewerId);
                event(new PostApproved($approval->fresh()));
            }

            return $approval->fresh();
        }

        $approval->approve($reviewerId, $comment);

        ActivityLog::record('post_approved', 'approvals', 'Post approved', ['approval_uuid' => $approval->uuid], $approval->tenant_id, $reviewerId);

        event(new PostApproved($approval->fresh()));

        return $approval;
    }

    public function reject(PostApproval $approval, int $reviewerId, string $reason): PostApproval
    {
        // Reject active workflow for this post if one exists
        $workflow = ApprovalWorkflow::where('post_id', $approval->post_id)
            ->whereIn('status', ['pending', 'in_review'])
            ->first();

        $workflow?->reject($reviewerId, $reason);

        $approval->reject($reviewerId, $reason);

        ActivityLog::record('post_rejected', 'approvals', "Post rejected: {$reason}", ['approval_uuid' => $approval->uuid], $approval->tenant_id, $reviewerId);

        event(new PostRejected($approval->fresh()));

        return $approval;
    }

    public function configureWorkflow(int $tenantId, int $postId, array $stages, int $createdBy): ApprovalWorkflow
    {
        return ApprovalWorkflow::updateOrCreate(
            ['tenant_id' => $tenantId, 'post_id' => $postId],
            [
                'stages'        => $stages,
                'current_stage' => 1,
                'status'        => 'pending',
                'created_by'    => $createdBy,
            ]
        );
    }

    public function pendingForTenant(int $tenantId, int $perPage = 20): LengthAwarePaginator
    {
        return PostApproval::where('tenant_id', $tenantId)
            ->pending()
            ->with(['post', 'requester'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function allForTenant(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        return PostApproval::where('tenant_id', $tenantId)
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['requested_by']), fn ($q) => $q->where('requested_by', $filters['requested_by']))
            ->with(['post', 'requester', 'reviewer'])
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function stats(int $tenantId): array
    {
        $base = PostApproval::where('tenant_id', $tenantId);
        return [
            'pending'  => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
        ];
    }

    private function startWorkflowIfConfigured(Post $post, int $requesterId, ?string $comment): void
    {
        // Check if a pre-configured workflow template exists for this tenant
        $workflow = ApprovalWorkflow::where('tenant_id', $post->tenant_id)
            ->whereNull('post_id')  // templates have no post_id
            ->where('status', 'template')
            ->first();

        if (! $workflow) {
            return;
        }

        ApprovalWorkflow::create([
            'tenant_id'     => $post->tenant_id,
            'post_id'       => $post->id,
            'stages'        => $workflow->stages,
            'current_stage' => 1,
            'status'        => 'in_review',
            'created_by'    => $requesterId,
        ]);
    }
}
