<?php

namespace App\Services\Post;

use App\Models\Hashtag;
use App\Models\Post;

class HashtagService
{
    public function syncHashtags(Post $post, int $tenantId, array $tags): void
    {
        if (empty($tags)) {
            $post->hashtags()->detach();
            return;
        }

        $ids = [];
        foreach ($tags as $tag) {
            $tag = strtolower(ltrim(trim($tag), '#'));
            if (!$tag) continue;

            $hashtag = Hashtag::firstOrCreate(
                ['tenant_id' => $tenantId, 'hashtag' => $tag],
                ['usage_count' => 0]
            );

            $ids[] = $hashtag->id;
        }

        $post->hashtags()->sync($ids);

        Hashtag::whereIn('id', $ids)->increment('usage_count');
    }

    public function suggestions(int $tenantId, string $query = '', int $limit = 20): array
    {
        return Hashtag::forTenant($tenantId)
            ->when($query, fn ($q) => $q->where('hashtag', 'like', "%{$query}%"))
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get(['hashtag', 'usage_count', 'is_trending'])
            ->toArray();
    }

    public function trending(int $tenantId, int $limit = 10): array
    {
        return Hashtag::forTenant($tenantId)
            ->trending()
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function groups(int $tenantId): array
    {
        return Hashtag::forTenant($tenantId)
            ->whereNotNull('group_name')
            ->select('group_name')
            ->distinct()
            ->pluck('group_name')
            ->toArray();
    }

    public function byGroup(int $tenantId, string $group): array
    {
        return Hashtag::forTenant($tenantId)
            ->group($group)
            ->orderByDesc('usage_count')
            ->get()
            ->toArray();
    }
}
