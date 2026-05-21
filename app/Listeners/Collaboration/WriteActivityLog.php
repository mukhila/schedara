<?php

namespace App\Listeners\Collaboration;

use App\Events\Collaboration\CommentAdded;
use App\Events\Collaboration\PostApprovalRequested;
use App\Events\Collaboration\PostApproved;
use App\Events\Collaboration\PostRejected;
use App\Events\Collaboration\TaskAssigned;
use App\Events\Collaboration\TaskCompleted;
use App\Models\ActivityLog;

class WriteActivityLog
{
    public function handleTaskAssigned(TaskAssigned $event): void
    {
        ActivityLog::record(
            action:      'task_assigned',
            module:      'tasks',
            description: "{$event->assignedBy->name} assigned \"{$event->task->title}\"",
            properties:  ['task_uuid' => $event->task->uuid, 'assigned_to' => $event->task->assigned_to],
            tenantId:    $event->task->tenant_id,
            userId:      $event->assignedBy->id,
            subject:     $event->task,
        );
    }

    public function handleTaskCompleted(TaskCompleted $event): void
    {
        ActivityLog::record(
            action:      'task_completed',
            module:      'tasks',
            description: "Task \"{$event->task->title}\" marked as completed",
            properties:  ['task_uuid' => $event->task->uuid],
            tenantId:    $event->task->tenant_id,
            subject:     $event->task,
        );
    }

    public function handleApprovalRequested(PostApprovalRequested $event): void
    {
        ActivityLog::record(
            action:      'approval_requested',
            module:      'approvals',
            description: "Post \"{$event->post->title}\" submitted for approval",
            properties:  ['approval_uuid' => $event->approval->uuid],
            tenantId:    $event->approval->tenant_id,
            subject:     $event->approval,
        );
    }

    public function handlePostApproved(PostApproved $event): void
    {
        ActivityLog::record(
            action:      'post_approved',
            module:      'approvals',
            description: "Post approved by {$event->approval->reviewer?->name}",
            properties:  ['approval_uuid' => $event->approval->uuid],
            tenantId:    $event->approval->tenant_id,
            subject:     $event->approval,
        );
    }

    public function handlePostRejected(PostRejected $event): void
    {
        ActivityLog::record(
            action:      'post_rejected',
            module:      'approvals',
            description: "Post rejected: {$event->approval->reviewer_comment}",
            properties:  ['approval_uuid' => $event->approval->uuid],
            tenantId:    $event->approval->tenant_id,
            subject:     $event->approval,
        );
    }

    public function handleCommentAdded(CommentAdded $event): void
    {
        $comment = $event->comment;
        $context = $comment->post_id ? "post #{$comment->post_id}" : "task #{$comment->task_id}";

        ActivityLog::record(
            action:      'comment_added',
            module:      'comments',
            description: "{$comment->author->name} commented on {$context}",
            properties:  ['comment_uuid' => $comment->uuid],
            tenantId:    $comment->tenant_id,
            subject:     $comment,
        );
    }
}
