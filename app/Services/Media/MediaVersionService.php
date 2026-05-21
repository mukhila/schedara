<?php

namespace App\Services\Media;

use App\Models\MediaActivityLog;
use App\Models\MediaLibrary;
use App\Models\MediaVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaVersionService
{
    public function __construct(private readonly MediaUploadService $uploadService) {}

    public function createVersion(MediaLibrary $media, UploadedFile $file, int $userId, ?string $note = null): MediaVersion
    {
        $disk     = $media->disk;
        $ext      = strtolower($file->getClientOriginalExtension());
        $dir      = 'uploads/versions/' . $media->uuid;
        $filename = Str::uuid() . '.' . $ext;
        $path     = $file->storeAs($dir, $filename, $disk);
        $url      = Storage::disk($disk)->url($path);
        $newVer   = ($media->version ?? 1) + 1;

        // Archive current version
        MediaVersion::create([
            'media_file_id' => $media->id,
            'created_by'    => $userId,
            'version'       => $media->version,
            'file_path'     => $media->s3_key,
            'file_url'      => $media->url,
            'file_size'     => $media->size,
            'change_note'   => 'Previous version before v' . $newVer,
        ]);

        // Update media to point to new file
        $media->update([
            's3_key'  => $path,
            'url'     => $url,
            'version' => $newVer,
            'size'    => $file->getSize(),
        ]);

        MediaActivityLog::record($media, 'version_created', 'success', ['version' => $newVer], $note);

        return MediaVersion::create([
            'media_file_id' => $media->id,
            'created_by'    => $userId,
            'version'       => $newVer,
            'file_path'     => $path,
            'file_url'      => $url,
            'file_size'     => $file->getSize(),
            'change_note'   => $note,
        ]);
    }

    public function restore(MediaLibrary $media, int $versionNumber, int $userId): MediaLibrary
    {
        $version = MediaVersion::where('media_file_id', $media->id)
            ->where('version', $versionNumber)
            ->firstOrFail();

        $media->update([
            's3_key'  => $version->file_path,
            'url'     => $version->file_url,
            'size'    => $version->file_size,
            'version' => $version->version,
        ]);

        MediaActivityLog::record($media, 'version_restored', 'success', ['restored_to' => $versionNumber]);

        return $media->fresh();
    }
}
