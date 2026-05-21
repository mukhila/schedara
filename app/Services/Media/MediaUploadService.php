<?php

namespace App\Services\Media;

use App\DTOs\Media\UploadMediaDTO;
use App\Events\Media\MediaUploaded;
use App\Jobs\Media\CompressVideoJob;
use App\Jobs\Media\OptimizeImageJob;
use App\Models\MediaActivityLog;
use App\Models\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Media\DuplicateDetectionService;

class MediaUploadService
{
    private const TYPE_MAP = [
        'image'    => ['jpg','jpeg','png','gif','webp','svg','bmp','tiff'],
        'video'    => ['mp4','mov','avi','webm','mkv','flv'],
        'audio'    => ['mp3','wav','ogg','aac','flac'],
        'document' => ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv'],
    ];

    public function upload(UploadMediaDTO $dto): MediaLibrary
    {
        $disk      = config('filesystems.default', 'local');
        $ext       = strtolower($dto->file->getClientOriginalExtension());
        $mediaType = $this->resolveType($ext);
        $fileHash  = hash_file('sha256', $dto->file->getRealPath());
        $dir       = "uploads/{$mediaType}s";
        $filename  = Str::uuid() . '.' . $ext;
        $path      = $dto->file->storeAs($dir, $filename, $disk);
        $url       = Storage::disk($disk)->url($path);

        $media = MediaLibrary::create([
            'tenant_id'           => $dto->tenantId,
            'user_id'             => $dto->userId,
            'folder_id'           => $dto->folderId,
            'name'                => pathinfo($dto->file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name'       => $dto->file->getClientOriginalName(),
            'disk'                => $disk,
            's3_key'              => $path,
            'url'                 => $url,
            'mime_type'           => $dto->file->getMimeType(),
            'extension'           => $ext,
            'file_hash'           => $fileHash,
            'type'                => $mediaType,
            'size'                => $dto->file->getSize(),
            'alt_text'            => $dto->altText,
            'optimization_status' => $mediaType === 'image' ? 'pending' : 'na',
            'compression_status'  => $mediaType === 'video' ? 'pending' : 'na',
            'approval_status'     => $dto->requestApproval ? 'pending' : 'draft',
            'version'             => 1,
        ]);

        if (!empty($dto->tags)) {
            $this->syncTags($media, $dto->tenantId, $dto->tags);
        }

        MediaActivityLog::record($media, 'uploaded', 'success', ['size' => $dto->file->getSize()]);
        event(new MediaUploaded($media));

        if ($mediaType === 'image') {
            OptimizeImageJob::dispatch($media);
        } elseif ($mediaType === 'video') {
            CompressVideoJob::dispatch($media);
        }

        return $media;
    }

    public function delete(MediaLibrary $media): void
    {
        Storage::disk($media->disk)->delete($media->s3_key);
        if ($media->thumbnail_path) {
            Storage::disk($media->disk)->delete($media->thumbnail_path);
        }
        MediaActivityLog::record($media, 'deleted', 'success');
        $media->delete();
    }

    public function move(MediaLibrary $media, ?int $folderId): MediaLibrary
    {
        $media->update(['folder_id' => $folderId]);
        MediaActivityLog::record($media, 'moved', 'success', ['folder_id' => $folderId]);
        return $media;
    }

    public function toggleFavorite(MediaLibrary $media): MediaLibrary
    {
        $media->update(['is_favorite' => !$media->is_favorite]);
        return $media->fresh();
    }

    public function generateShareLink(MediaLibrary $media, ?int $expiresInHours = 72): MediaLibrary
    {
        $media->update([
            'share_token'      => Str::random(48),
            'share_expires_at' => now()->addHours($expiresInHours),
        ]);
        return $media->fresh();
    }

    public function resolveType(string $extension): string
    {
        foreach (self::TYPE_MAP as $type => $extensions) {
            if (in_array($extension, $extensions)) return $type;
        }
        return 'document';
    }

    private function syncTags(MediaLibrary $media, int $tenantId, array $tagNames): void
    {
        $ids = [];
        foreach ($tagNames as $name) {
            $slug = Str::slug($name);
            $tag  = \App\Models\MediaTag::firstOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $slug],
                ['tag_name' => $name, 'color' => '#' . substr(md5($slug), 0, 6)]
            );
            $ids[] = $tag->id;
        }
        $media->mediaTags()->sync($ids);
        \App\Models\MediaTag::whereIn('id', $ids)->increment('usage_count');
    }
}
