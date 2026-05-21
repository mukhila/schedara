<?php

namespace App\Listeners\Post;

use App\Events\Post\CaptionGenerated;
use App\Events\Post\MediaProcessed;
use App\Events\Post\PostFailed;
use App\Events\Post\PostPublished;
use App\Events\Post\PostScheduled;
use App\Models\PostLog;

class LogPostActivity
{
    public function handleScheduled(PostScheduled $event): void
    {
        PostLog::record($event->post, 'scheduled_event', 'success', [
            'scheduled_at' => $event->post->scheduled_at?->toIso8601String(),
        ]);
    }

    public function handlePublished(PostPublished $event): void
    {
        PostLog::record($event->post, 'published_event', 'success', [
            'published_at' => now()->toIso8601String(),
        ]);
    }

    public function handleFailed(PostFailed $event): void
    {
        PostLog::record($event->post, 'failed_event', 'error', [], null, $event->reason);
    }

    public function handleMediaProcessed(MediaProcessed $event): void
    {
        PostLog::record($event->media->post, 'media_processed', 'success', [
            'media_uuid' => $event->media->uuid,
        ]);
    }

    public function handleCaptionGenerated(CaptionGenerated $event): void
    {
        PostLog::record($event->post, 'caption_generated', 'success', [
            'platform' => $event->platform,
        ]);
    }
}
