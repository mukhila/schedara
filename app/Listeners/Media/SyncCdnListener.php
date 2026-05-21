<?php

namespace App\Listeners\Media;

use App\Events\Media\ContentApproved;
use App\Events\Media\MediaOptimized;
use App\Events\Media\MediaUploaded;
use App\Events\Media\VideoCompressed;
use App\Models\MediaActivityLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncCdnListener
{
    public function handleUploaded(MediaUploaded $event): void
    {
        $this->warmCache($event->media->publicUrl(), $event->media->id);
    }

    public function handleOptimized(MediaOptimized $event): void
    {
        $this->invalidate($event->media->publicUrl(), $event->media->id);

        if ($event->media->thumbnailPublicUrl()) {
            $this->invalidate($event->media->thumbnailPublicUrl(), $event->media->id);
        }
    }

    public function handleCompressed(VideoCompressed $event): void
    {
        $this->invalidate($event->media->publicUrl(), $event->media->id);
    }

    public function handleApproved(ContentApproved $event): void
    {
        $this->warmCache($event->media->publicUrl(), $event->media->id);
    }

    private function warmCache(string $url, int $mediaId): void
    {
        if (!config('media.cdn_warm_url')) return;

        try {
            Http::timeout(5)->get($url);
            MediaActivityLog::record(null, 'cdn_warmed', 'success', ['url' => $url]);
        } catch (\Throwable $e) {
            Log::warning("CDN cache warm failed for media #{$mediaId}: {$e->getMessage()}");
        }
    }

    private function invalidate(string $url, int $mediaId): void
    {
        if (!config('media.cdn_invalidation_url')) return;

        try {
            Http::timeout(5)->post(config('media.cdn_invalidation_url'), ['url' => $url]);
            MediaActivityLog::record(null, 'cdn_invalidated', 'success', ['url' => $url]);
        } catch (\Throwable $e) {
            Log::warning("CDN invalidation failed for media #{$mediaId}: {$e->getMessage()}");
        }
    }
}
