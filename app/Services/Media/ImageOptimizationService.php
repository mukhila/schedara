<?php

namespace App\Services\Media;

use App\Events\Media\MediaOptimized;
use App\Models\MediaActivityLog;
use App\Models\MediaLibrary;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageOptimizationService
{
    public function optimize(MediaLibrary $media): MediaLibrary
    {
        if ($media->type !== 'image') return $media;

        $media->update(['optimization_status' => 'processing']);

        try {
            $disk     = $media->disk;
            $contents = Storage::disk($disk)->get($media->s3_key);
            $img      = Image::read($contents);

            // Store dimensions
            $media->update(['width' => $img->width(), 'height' => $img->height()]);

            // Generate thumbnail (400px wide)
            $this->generateThumbnail($media, $img, $disk);

            // Compress main image (quality 85)
            $ext      = strtolower($media->extension);
            $encoded  = match ($ext) {
                'png'  => $img->toPng()->toString(),
                'webp' => $img->toWebp(85)->toString(),
                default => $img->toJpeg(85)->toString(),
            };

            Storage::disk($disk)->put($media->s3_key, $encoded);

            $media->update([
                'optimization_status' => 'done',
                'size'                => strlen($encoded),
            ]);

            MediaActivityLog::record($media, 'optimized', 'success', [
                'original_size' => $media->size,
                'new_size'      => strlen($encoded),
            ]);

            event(new MediaOptimized($media->fresh()));
        } catch (\Throwable $e) {
            $media->update(['optimization_status' => 'failed']);
            MediaActivityLog::record($media, 'optimization_failed', 'error', [], $e->getMessage());
        }

        return $media->fresh();
    }

    public function generateThumbnailOnly(MediaLibrary $media): void
    {
        if ($media->type !== 'image' || $media->thumbnail_path) return;

        try {
            $disk     = $media->disk;
            $contents = Storage::disk($disk)->get($media->s3_key);
            $img      = Image::read($contents);
            $this->generateThumbnail($media, $img, $disk);
        } catch (\Throwable $e) {
            MediaActivityLog::record($media, 'thumbnail_failed', 'error', [], $e->getMessage());
        }
    }

    private function generateThumbnail(MediaLibrary $media, $img, string $disk): void
    {
        $thumb     = Image::read($img->toJpeg()->toString())->scale(400);
        $thumbPath = 'uploads/thumbnails/' . basename($media->s3_key);
        $thumbUrl  = Storage::disk($disk)->url($thumbPath);

        Storage::disk($disk)->put($thumbPath, $thumb->toJpeg(75)->toString());

        $media->update([
            'thumbnail_path' => $thumbPath,
            'thumbnail_url'  => $thumbUrl,
        ]);
    }
}
