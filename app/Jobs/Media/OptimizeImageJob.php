<?php

namespace App\Jobs\Media;

use App\Models\MediaLibrary;
use App\Services\Media\ImageOptimizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeImageJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public readonly MediaLibrary $media) {}

    public function uniqueId(): string { return "optimize-image-{$this->media->id}"; }

    public function handle(ImageOptimizationService $service): void
    {
        $service->optimize($this->media);
    }

    public function failed(\Throwable $e): void
    {
        $this->media->update(['optimization_status' => 'failed']);
    }
}
