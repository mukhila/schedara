<?php

namespace App\Services\Post;

use App\Jobs\Post\ProcessMediaJob;
use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function upload(Post $post, UploadedFile $file, int $sortOrder = 0): PostMedia
    {
        $uuid     = (string) Str::uuid();
        $disk     = config('filesystems.default', 'local');
        $dir      = "posts/{$post->uuid}/media";
        $filename = $uuid . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs($dir, $filename, $disk);

        $media = PostMedia::create([
            'uuid'               => $uuid,
            'post_id'            => $post->id,
            'media_type'         => $this->resolveMediaType($file->getMimeType()),
            'disk'               => $disk,
            'file_path'          => $path,
            'file_url'           => Storage::disk($disk)->url($path),
            'mime_type'          => $file->getMimeType(),
            'file_size'          => $file->getSize(),
            'sort_order'         => $sortOrder,
            'processing_status'  => 'pending',
        ]);

        ProcessMediaJob::dispatch($media);

        return $media;
    }

    public function delete(PostMedia $media): void
    {
        Storage::disk($media->disk)->delete($media->file_path);

        if ($media->thumbnail_path) {
            Storage::disk($media->disk)->delete($media->thumbnail_path);
        }

        if ($media->watermark_path) {
            Storage::disk($media->disk)->delete($media->watermark_path);
        }

        $media->delete();
    }

    public function reorder(Post $post, array $orderedUuids): void
    {
        foreach ($orderedUuids as $index => $uuid) {
            PostMedia::where('post_id', $post->id)
                ->where('uuid', $uuid)
                ->update(['sort_order' => $index]);
        }
    }

    private function resolveMediaType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'other';
    }
}
