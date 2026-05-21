<?php

namespace App\Services\Post;

use App\Models\PostMedia;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class WatermarkService
{
    public function apply(PostMedia $media): PostMedia
    {
        if ($media->media_type !== 'image') {
            return $media;
        }

        $watermarkText = config('schedara.watermark_text', 'Schedara');
        $sourceDisk    = $media->disk;
        $sourcePath    = $media->file_path;

        $contents = Storage::disk($sourceDisk)->get($sourcePath);
        $img      = Image::read($contents);

        $img->text($watermarkText, $img->width() - 20, $img->height() - 20, function ($font) {
            $font->size(18);
            $font->color([255, 255, 255, 100]);
            $font->align('right');
            $font->valign('bottom');
        });

        $wmDir      = dirname($sourcePath) . '/watermarked';
        $wmFilename = basename($sourcePath);
        $wmPath     = $wmDir . '/' . $wmFilename;

        Storage::disk($sourceDisk)->put($wmPath, $img->toJpeg(90)->toString());

        $media->update([
            'watermark_path' => $wmPath,
            'is_watermarked' => true,
        ]);

        return $media->fresh();
    }
}
