<?php

namespace App\Jobs\Media;

use App\Models\MediaLibrary;
use App\Services\Media\VideoCompressionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompressVideoJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 3600;

    public function __construct(public readonly MediaLibrary $media) {}

    public function uniqueId(): string { return "compress-video-{$this->media->id}"; }

    public function handle(VideoCompressionService $service): void
    {
        $service->compress($this->media);
    }

    public function failed(\Throwable $e): void
    {
        $this->media->update(['compression_status' => 'failed']);
    }
}
