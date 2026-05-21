<?php

namespace App\Repositories;

use App\Models\MediaLibrary;
use Illuminate\Pagination\LengthAwarePaginator;

class MediaFileRepository
{
    public function paginate(int $tenantId, array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $q = MediaLibrary::where('tenant_id', $tenantId)
            ->with(['folder', 'mediaTags', 'approval'])
            ->orderByDesc('created_at');

        if (!empty($filters['search'])) {
            $q->search($filters['search']);
        }

        if (!empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        if (array_key_exists('folder_id', $filters)) {
            $q->where('folder_id', $filters['folder_id'] ?: null);
        }

        if (!empty($filters['tag'])) {
            $q->whereHas('mediaTags', fn ($t) => $t->where('slug', $filters['tag']));
        }

        if (!empty($filters['approval_status'])) {
            $q->where('approval_status', $filters['approval_status']);
        }

        if (isset($filters['favorites']) && $filters['favorites']) {
            $q->favorites();
        }

        if (!empty($filters['sort'])) {
            match ($filters['sort']) {
                'name'     => $q->orderBy('name'),
                'size'     => $q->orderByDesc('size'),
                'oldest'   => $q->orderBy('created_at'),
                default    => $q->orderByDesc('created_at'),
            };
        }

        return $q->paginate($perPage);
    }

    public function findByUuid(string $uuid, int $tenantId): ?MediaLibrary
    {
        return MediaLibrary::where('uuid', $uuid)
            ->where('tenant_id', $tenantId)
            ->with(['folder', 'mediaTags', 'approvals.requester', 'approvals.approver', 'versions.creator', 'activityLogs'])
            ->first();
    }

    public function stats(int $tenantId): array
    {
        $base = MediaLibrary::where('tenant_id', $tenantId);

        return [
            'total'        => (clone $base)->count(),
            'images'       => (clone $base)->where('type', 'image')->count(),
            'videos'       => (clone $base)->where('type', 'video')->count(),
            'documents'    => (clone $base)->where('type', 'document')->count(),
            'total_size'   => (clone $base)->sum('size'),
            'pending'      => (clone $base)->where('approval_status', 'pending')->count(),
            'favorites'    => (clone $base)->where('is_favorite', true)->count(),
        ];
    }
}
