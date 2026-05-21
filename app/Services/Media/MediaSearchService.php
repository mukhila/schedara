<?php

namespace App\Services\Media;

use App\Models\MediaLibrary;
use Illuminate\Pagination\LengthAwarePaginator;

class MediaSearchService
{
    private const DEFAULT_PER_PAGE = 24;

    public function search(int $tenantId, array $filters, int $perPage = self::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        $q = MediaLibrary::where('tenant_id', $tenantId)
            ->with(['folder', 'mediaTags'])
            ->orderByDesc('created_at');

        // Full-text keyword search across name, original_name, alt_text
        if (!empty($filters['q'])) {
            $term = $filters['q'];
            $q->where(fn ($sub) =>
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('original_name', 'like', "%{$term}%")
                    ->orWhere('alt_text', 'like', "%{$term}%")
            );
        }

        if (!empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        if (!empty($filters['extension'])) {
            $q->where('extension', strtolower($filters['extension']));
        }

        if (!empty($filters['tag'])) {
            $q->whereHas('mediaTags', fn ($t) => $t->where('slug', $filters['tag']));
        }

        if (!empty($filters['folder_id'])) {
            $q->where('folder_id', $filters['folder_id']);
        }

        if (!empty($filters['approval_status'])) {
            $q->where('approval_status', $filters['approval_status']);
        }

        if (isset($filters['favorites']) && $filters['favorites']) {
            $q->where('is_favorite', true);
        }

        if (!empty($filters['uploaded_by'])) {
            $q->where('user_id', $filters['uploaded_by']);
        }

        if (!empty($filters['date_from'])) {
            $q->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $q->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['min_size'])) {
            $q->where('size', '>=', $filters['min_size']);
        }

        if (!empty($filters['max_size'])) {
            $q->where('size', '<=', $filters['max_size']);
        }

        if (!empty($filters['sort'])) {
            match ($filters['sort']) {
                'name'       => $q->orderBy('name'),
                'size_asc'   => $q->orderBy('size'),
                'size_desc'  => $q->orderByDesc('size'),
                'oldest'     => $q->orderBy('created_at'),
                default      => $q->orderByDesc('created_at'),
            };
        }

        return $q->paginate($perPage);
    }

    public function findDuplicates(int $tenantId): array
    {
        return MediaLibrary::where('tenant_id', $tenantId)
            ->whereNotNull('file_hash')
            ->select('file_hash')
            ->groupBy('file_hash')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('file_hash')
            ->mapWithKeys(fn ($hash) => [
                $hash => MediaLibrary::where('tenant_id', $tenantId)
                    ->where('file_hash', $hash)
                    ->with('folder')
                    ->get(['id', 'uuid', 'name', 'type', 'size', 'created_at', 'folder_id', 'file_hash'])
                    ->toArray(),
            ])
            ->toArray();
    }

    public function recentUploads(int $tenantId, int $limit = 10): array
    {
        return MediaLibrary::where('tenant_id', $tenantId)
            ->with(['folder', 'mediaTags'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
