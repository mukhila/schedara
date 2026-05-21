<?php

namespace App\Jobs\Post;

use App\Events\Post\MediaProcessed;
use App\Models\PostMedia;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProcessMediaJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180;

    public function __construct(public readonly PostMedia $media) {}

    public function handle(): void
    {
        $this->media->update(['processing_status' => 'processing']);

        try {
            if ($this->media->media_type === 'image') {
                $this->processImage();
            }

            $this->media->update(['processing_status' => 'done']);
            event(new MediaProcessed($this->media->fresh()));
        } catch (\Throwable $e) {
            $this->media->update(['processing_status' => 'failed', 'metadata' => ['error' => $e->getMessage()]]);
            throw $e;
        }
    }

    private function processImage(): void
    {
        $contents = Storage::disk($this->media->disk)->get($this->media->file_path);
        $img      = Image::read($contents);

        $this->media->update([
            'width'  => $img->width(),
            'height' => $img->height(),
        ]);

        // Generate thumbnail
        $thumb     = Image::read($contents)->scale(400);
        $thumbDir  = dirname($this->media->file_path) . '/thumbs';
        $thumbPath = $thumbDir . '/' . basename($this->media->file_path);

        Storage::disk($this->media->disk)->put($thumbPath, $thumb->toJpeg(80)->toString());

        $this->media->update([
            'thumbnail_path' => $thumbPath,
            'thumbnail_url'  => Storage::disk($this->media->disk)->url($thumbPath),
        ]);
    }
}
