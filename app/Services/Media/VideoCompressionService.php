<?php

namespace App\Services\Media;

use App\Events\Media\VideoCompressed;
use App\Models\MediaActivityLog;
use App\Models\MediaLibrary;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;

class VideoCompressionService
{
    public function compress(MediaLibrary $media): MediaLibrary
    {
        if ($media->type !== 'video') return $media;

        $media->update(['compression_status' => 'processing']);

        try {
            $ffmpeg    = $this->buildFFMpeg();
            $disk      = $media->disk;
            $localPath = $this->download($media, $disk);

            $outputPath = sys_get_temp_dir() . '/compressed_' . basename($localPath, '.' . $media->extension) . '.mp4';

            $video  = $ffmpeg->open($localPath);
            $format = new X264('aac');
            $format->setKiloBitrate(1500)->setAudioKiloBitrate(128);

            $video->save($format, $outputPath);

            // Extract duration via FFProbe
            $ffprobe  = FFProbe::create($this->ffmpegConfig());
            $duration = (int) $ffprobe->format($localPath)->get('duration', 0);

            // Extract thumbnail at 1s
            $thumbTmpPath = sys_get_temp_dir() . '/thumb_' . basename($localPath) . '.jpg';
            $video->frame(TimeCode::fromSeconds(min(1, $duration)))->save($thumbTmpPath);

            // Store compressed file
            $compressedKey = 'uploads/compressed/' . basename($media->s3_key, '.' . $media->extension) . '.mp4';
            Storage::disk($disk)->put($compressedKey, file_get_contents($outputPath));

            // Store thumbnail
            $thumbKey = 'uploads/thumbnails/' . basename($media->s3_key) . '.jpg';
            Storage::disk($disk)->put($thumbKey, file_get_contents($thumbTmpPath));

            $newSize = filesize($outputPath);

            $media->update([
                'compression_status' => 'done',
                'duration'           => $duration,
                'thumbnail_path'     => $thumbKey,
                'thumbnail_url'      => Storage::disk($disk)->url($thumbKey),
                'size'               => $newSize,
                'metadata'           => array_merge($media->metadata ?? [], [
                    'compressed_key' => $compressedKey,
                    'original_size'  => $media->size,
                    'compressed_size'=> $newSize,
                ]),
            ]);

            // Cleanup temp files
            @unlink($localPath);
            @unlink($outputPath);
            @unlink($thumbTmpPath);

            MediaActivityLog::record($media, 'compressed', 'success', [
                'original_size'  => $media->size,
                'compressed_size'=> $newSize,
            ]);

            event(new VideoCompressed($media->fresh()));
        } catch (\Throwable $e) {
            $media->update(['compression_status' => 'failed']);
            MediaActivityLog::record($media, 'compression_failed', 'error', [], $e->getMessage());
        }

        return $media->fresh();
    }

    public function extractThumbnail(MediaLibrary $media): void
    {
        if ($media->type !== 'video' || $media->thumbnail_path) return;

        try {
            $disk      = $media->disk;
            $localPath = $this->download($media, $disk);
            $ffmpeg    = $this->buildFFMpeg();
            $ffprobe   = FFProbe::create($this->ffmpegConfig());
            $duration  = (int) $ffprobe->format($localPath)->get('duration', 0);

            $thumbTmp = sys_get_temp_dir() . '/thumb_' . basename($localPath) . '.jpg';
            $ffmpeg->open($localPath)->frame(TimeCode::fromSeconds(min(1, $duration)))->save($thumbTmp);

            $thumbKey = 'uploads/thumbnails/' . basename($media->s3_key) . '.jpg';
            Storage::disk($disk)->put($thumbKey, file_get_contents($thumbTmp));

            $media->update([
                'thumbnail_path' => $thumbKey,
                'thumbnail_url'  => Storage::disk($disk)->url($thumbKey),
                'duration'       => $duration,
            ]);

            @unlink($localPath);
            @unlink($thumbTmp);

            MediaActivityLog::record($media, 'thumbnail_extracted', 'success');
        } catch (\Throwable $e) {
            MediaActivityLog::record($media, 'thumbnail_extraction_failed', 'error', [], $e->getMessage());
        }
    }

    private function download(MediaLibrary $media, string $disk): string
    {
        $tmpPath = sys_get_temp_dir() . '/' . basename($media->s3_key);
        file_put_contents($tmpPath, Storage::disk($disk)->get($media->s3_key));
        return $tmpPath;
    }

    private function buildFFMpeg(): FFMpeg
    {
        return FFMpeg::create($this->ffmpegConfig());
    }

    private function ffmpegConfig(): array
    {
        return [
            'ffmpeg.binaries'  => config('media.ffmpeg_path', env('FFMPEG_PATH', '/usr/bin/ffmpeg')),
            'ffprobe.binaries' => config('media.ffprobe_path', env('FFPROBE_PATH', '/usr/bin/ffprobe')),
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ];
    }
}
