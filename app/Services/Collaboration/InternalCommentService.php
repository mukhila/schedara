<?php

namespace App\Services\Collaboration;

use App\Events\Collaboration\CommentAdded;
use App\Models\ActivityLog;
use App\Models\InternalComment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InternalCommentService
{
    public function add(array $data, int $tenantId, int $userId): InternalComment
    {
        // Parse @mentions from comment text
        preg_match_all('/@\[(\d+)\]/', $data['comment'], $matches);
        $mentions = array_unique(array_map('intval', $matches[1]));

        $comment = InternalComment::create([
            'uuid'        => Str::uuid(),
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'post_id'     => $data['post_id'] ?? null,
            'task_id'     => $data['task_id'] ?? null,
            'parent_id'   => $data['parent_id'] ?? null,
            'comment'     => $data['comment'],
            'attachments' => $data['attachments'] ?? null,
            'mentions'    => $mentions ?: null,
        ]);

        event(new CommentAdded($comment));

        return $comment->load('author');
    }

    public function update(InternalComment $comment, string $text): InternalComment
    {
        $comment->update(['comment' => $text]);
        ActivityLog::record('comment_edited', 'comments', 'Comment edited', ['comment_uuid' => $comment->uuid]);
        return $comment;
    }

    public function delete(InternalComment $comment): void
    {
        $comment->delete();
        ActivityLog::record('comment_deleted', 'comments', 'Comment deleted', ['comment_uuid' => $comment->uuid]);
    }

    public function react(InternalComment $comment, int $userId, string $emoji): array
    {
        $reactions = $comment->reactions ?? [];
        $alreadyReacted = in_array($userId, $reactions[$emoji] ?? []);

        if ($alreadyReacted) {
            $comment->removeReaction($userId, $emoji);
        } else {
            $comment->addReaction($userId, $emoji);
        }

        return $comment->fresh()->reactions ?? [];
    }

    public function forPost(int $postId, int $tenantId): Collection
    {
        return InternalComment::where('tenant_id', $tenantId)
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->with(['author', 'replies.author'])
            ->orderBy('created_at')
            ->get();
    }

    public function forTask(int $taskId, int $tenantId): Collection
    {
        return InternalComment::where('tenant_id', $tenantId)
            ->where('task_id', $taskId)
            ->whereNull('parent_id')
            ->with(['author', 'replies.author'])
            ->orderBy('created_at')
            ->get();
    }
}
