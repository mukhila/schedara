<?php

namespace App\Jobs\Media;

use App\Models\MediaLibrary;
use App\Services\Media\ImageOptimizationService;
use App\Services\Media\VideoCompressionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateThumbnailJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly MediaLibrary $media) {}

    public function uniqueId(): string { return "generate-thumbnail-{$this->media->id}"; }

    public function handle(ImageOptimizationService $imageService, VideoCompressionService $videoService): void
    {
        match ($this->media->type) {
            'image' => $imageService->generateThumbnailOnly($this->media),
            'video' => $videoService->extractThumbnail($this->media),
            default => null,
        };
    }

    public function failed(\Throwable $e): void
    {
        \App\Models\MediaActivityLog::record(
            $this->media,
            'thumbnail_generation_failed',
            'error',
            [],
            $e->getMessage()
        );
    }
}
