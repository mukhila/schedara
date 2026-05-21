<?php

namespace App\Listeners\Media;

use App\Events\Media\ContentApproved;
use App\Events\Media\ContentRejected;
use App\Events\Media\MediaOptimized;
use App\Events\Media\MediaUploaded;
use App\Events\Media\VideoCompressed;
use App\Models\MediaActivityLog;

class LogMediaActivity
{
    public function handleUploaded(MediaUploaded $event): void
    {
        MediaActivityLog::record($event->media, 'upload_event', 'success', [
            'size' => $event->media->size,
            'type' => $event->media->type,
        ]);
    }

    public function handleOptimized(MediaOptimized $event): void
    {
        MediaActivityLog::record($event->media, 'optimization_event', 'success');
    }

    public function handleCompressed(VideoCompressed $event): void
    {
        MediaActivityLog::record($event->media, 'compression_event', 'success', [
            'duration' => $event->media->duration,
        ]);
    }

    public function handleApproved(ContentApproved $event): void
    {
        MediaActivityLog::record($event->media, 'approval_event', 'success');
    }

    public function handleRejected(ContentRejected $event): void
    {
        MediaActivityLog::record($event->media, 'rejection_event', 'error');
    }
}
