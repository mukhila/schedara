<?php

namespace App\Services\Media;

use App\Models\MediaLibrary;
use App\Models\MediaTag;
use Illuminate\Support\Str;

class MediaTagService
{
    public function syncTags(MediaLibrary $media, int $tenantId, array $tagNames): void
    {
        $ids = [];
        foreach ($tagNames as $name) {
            $slug = Str::slug($name);
            if (!$slug) continue;

            $tag   = MediaTag::firstOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $slug],
                ['tag_name' => $name, 'color' => '#' . substr(md5($slug), 0, 6)]
            );
            $ids[] = $tag->id;
        }

        $media->mediaTags()->sync($ids);
        MediaTag::whereIn('id', $ids)->increment('usage_count');
    }

    public function suggestions(int $tenantId, string $query = '', int $limit = 20): array
    {
        return MediaTag::forTenant($tenantId)
            ->when($query, fn ($q) => $q->where('tag_name', 'like', "%{$query}%"))
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get(['id', 'tag_name', 'slug', 'color', 'usage_count'])
            ->toArray();
    }

    public function all(int $tenantId): array
    {
        return MediaTag::forTenant($tenantId)
            ->orderByDesc('usage_count')
            ->get(['id', 'tag_name', 'slug', 'color', 'usage_count'])
            ->toArray();
    }
}
