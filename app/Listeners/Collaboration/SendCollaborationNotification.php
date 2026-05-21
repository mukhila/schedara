<?php

namespace App\Listeners\Collaboration;

use App\Events\Collaboration\CommentAdded;
use App\Events\Collaboration\PostApprovalRequested;
use App\Events\Collaboration\PostApproved;
use App\Events\Collaboration\PostRejected;
use App\Events\Collaboration\TaskAssigned;
use App\Events\Collaboration\TaskCompleted;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class SendCollaborationNotification
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function handleTaskAssigned(TaskAssigned $event): void
    {
        $assignee = User::find($event->task->assigned_to);
        if (!$assignee) {
            return;
        }

        $this->notifications->send(
            user:      $assignee,
            type:      'task_assigned',
            category:  'collaboration',
            title:     'New task assigned',
            body:      "\"{$event->task->title}\" was assigned to you by {$event->assignedBy->name}.",
            payload:   ['task_uuid' => $event->task->uuid],
            actionUrl: url("/collaboration/tasks/{$event->task->uuid}"),
            tenantId:  $event->task->tenant_id,
        );
    }

    public function handleTaskCompleted(TaskCompleted $event): void
    {
        $creator = User::find($event->task->assigned_by);
        if (!$creator || $creator->id === auth()->id()) {
            return;
        }

        $this->notifications->send(
            user:      $creator,
            type:      'task_completed',
            category:  'collaboration',
            title:     'Task completed',
            body:      "\"{$event->task->title}\" has been marked as complete.",
            payload:   ['task_uuid' => $event->task->uuid],
            actionUrl: url("/collaboration/tasks/{$event->task->uuid}"),
            tenantId:  $event->task->tenant_id,
        );
    }

    public function handleApprovalRequested(PostApprovalRequested $event): void
    {
        // Notify all managers and admins who can approve
        TenantUser::where('tenant_id', $event->approval->tenant_id)
            ->whereIn('role', ['owner', 'admin', 'manager'])
            ->with('user')
            ->get()
            ->each(function (TenantUser $membership) use ($event) {
                if ($membership->user_id === $event->approval->requested_by) {
                    return;
                }
                $this->notifications->send(
                    user:      $membership->user,
                    type:      'approval_requested',
                    category:  'approval',
                    title:     'Post awaiting approval',
                    body:      "\"{$event->post->title}\" has been submitted for your review.",
                    payload:   ['approval_uuid' => $event->approval->uuid],
                    actionUrl: url("/collaboration/approvals/{$event->approval->uuid}"),
                    tenantId:  $event->approval->tenant_id,
                );
            });
    }

    public function handlePostApproved(PostApproved $event): void
    {
        $requester = User::find($event->approval->requested_by);
        if (!$requester) {
            return;
        }

        $this->notifications->send(
            user:      $requester,
            type:      'post_approved',
            category:  'approval',
            title:     'Post approved',
            body:      'Your post has been approved and is ready to publish.',
            payload:   ['approval_uuid' => $event->approval->uuid],
            actionUrl: url("/collaboration/approvals/{$event->approval->uuid}"),
            tenantId:  $event->approval->tenant_id,
        );
    }

    public function handlePostRejected(PostRejected $event): void
    {
        $requester = User::find($event->approval->requested_by);
        if (!$requester) {
            return;
        }

        $this->notifications->send(
            user:      $requester,
            type:      'post_rejected',
            category:  'approval',
            title:     'Post rejected',
            body:      "Your post was rejected. Reason: {$event->approval->reviewer_comment}",
            payload:   ['approval_uuid' => $event->approval->uuid],
            actionUrl: url("/collaboration/approvals/{$event->approval->uuid}"),
            priority:  'high',
            tenantId:  $event->approval->tenant_id,
        );
    }

    public function handleCommentAdded(CommentAdded $event): void
    {
        $comment = $event->comment;

        // Notify mentioned users
        foreach (($comment->mentions ?? []) as $userId) {
            $user = User::find($userId);
            if (!$user || $user->id === $comment->user_id) {
                continue;
            }

            $this->notifications->send(
                user:      $user,
                type:      'mention',
                category:  'collaboration',
                title:     'You were mentioned',
                body:      "{$comment->author->name} mentioned you in a comment.",
                payload:   ['comment_uuid' => $comment->uuid],
                actionUrl: $comment->post_id
                    ? url("/posts/{$comment->post->uuid}#comments")
                    : url("/collaboration/tasks/{$comment->task->uuid}#comments"),
                tenantId:  $comment->tenant_id,
            );
        }
    }
}
