<?php

namespace App\Jobs\Media;

use App\Models\MediaLibrary;
use App\Services\AI\MediaAiTaggingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AIContentTaggingJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(public readonly MediaLibrary $media) {}

    public function handle(MediaAiTaggingService $service): void
    {
        if (!$this->media->isImage()) return;
        $service->generateTags($this->media);
        $service->generateAltText($this->media);
    }
}
