<?php

namespace App\Services\Media;

use App\Models\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class DuplicateDetectionService
{
    public function computeHash(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

    public function findDuplicates(int $tenantId, string $hash): Collection
    {
        return MediaLibrary::where('tenant_id', $tenantId)
            ->where('file_hash', $hash)
            ->with(['folder', 'mediaTags'])
            ->get();
    }

    public function isDuplicate(int $tenantId, string $hash): bool
    {
        return MediaLibrary::where('tenant_id', $tenantId)
            ->where('file_hash', $hash)
            ->exists();
    }

    public function attachHash(MediaLibrary $media, string $hash): void
    {
        $media->update(['file_hash' => $hash]);
    }
}
