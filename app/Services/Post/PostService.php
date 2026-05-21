<?php

namespace App\Services\Post;

use App\DTOs\Post\CreatePostDTO;
use App\Events\Post\PostScheduled;
use App\Models\CalendarEvent;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\PostLog;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(
        private readonly HashtagService $hashtagService,
    ) {}

    public function list(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        $query = Post::where('tenant_id', $tenantId)
            ->with(['user', 'platformConfigs.socialAccount', 'hashtags', 'media'])
            ->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['platform'])) {
            $query->whereJsonContains('platforms', $filters['platform']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('content', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('caption', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['from'])) {
            $query->where('scheduled_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('scheduled_at', '<=', $filters['to']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function create(int $tenantId, int $userId, CreatePostDTO $dto): Post
    {
        $post = Post::create([
            'uuid'             => (string) Str::uuid(),
            'tenant_id'        => $tenantId,
            'user_id'          => $userId,
            'content'          => $dto->content,
            'caption'          => $dto->caption,
            'type'             => $dto->type,
            'status'           => $dto->status,
            'platforms'        => $dto->platforms,
            'scheduled_at'     => $dto->scheduledAt ? Carbon::parse($dto->scheduledAt, $dto->timezone)->utc() : null,
            'timezone'         => $dto->timezone,
            'title'            => $dto->title,
            'is_evergreen'     => $dto->isEvergreen,
            'auto_repost'      => $dto->autoRepost,
            'repost_frequency' => $dto->repostFrequency,
        ]);

        $this->syncPlatformConfigs($post, $dto);
        $this->hashtagService->syncHashtags($post, $tenantId, $dto->hashtags);
        $this->syncCalendarEvent($post);

        PostLog::record($post, 'created', 'success', [], null, "Post created with status: {$dto->status}");

        if ($post->status === 'scheduled' && $post->scheduled_at) {
            event(new PostScheduled($post));
        }

        return $post->load(['platformConfigs', 'hashtags', 'calendarEvent']);
    }

    public function update(Post $post, CreatePostDTO $dto): Post
    {
        $post->update([
            'content'          => $dto->content,
            'caption'          => $dto->caption,
            'type'             => $dto->type,
            'status'           => $dto->status,
            'platforms'        => $dto->platforms,
            'scheduled_at'     => $dto->scheduledAt ? Carbon::parse($dto->scheduledAt, $dto->timezone)->utc() : null,
            'timezone'         => $dto->timezone,
            'title'            => $dto->title,
            'is_evergreen'     => $dto->isEvergreen,
            'auto_repost'      => $dto->autoRepost,
            'repost_frequency' => $dto->repostFrequency,
        ]);

        $this->syncPlatformConfigs($post, $dto);
        $this->hashtagService->syncHashtags($post, $post->tenant_id, $dto->hashtags);
        $this->syncCalendarEvent($post);

        PostLog::record($post, 'updated', 'success');

        if ($post->status === 'scheduled' && $post->scheduled_at) {
            event(new PostScheduled($post));
        }

        return $post->fresh(['platformConfigs', 'hashtags', 'calendarEvent']);
    }

    public function delete(Post $post): void
    {
        $post->calendarEvent?->delete();
        $post->delete();
    }

    public function duplicate(Post $post): Post
    {
        $clone = $post->replicate(['uuid', 'status', 'scheduled_at', 'published_at']);
        $clone->uuid   = (string) Str::uuid();
        $clone->status = 'draft';
        $clone->save();

        foreach ($post->platformConfigs as $config) {
            $clone->platformConfigs()->create([
                'social_account_id' => $config->social_account_id,
                'platform'          => $config->platform,
                'content_override'  => $config->content_override,
                'first_comment'     => $config->first_comment,
                'status'            => 'pending',
            ]);
        }

        $clone->hashtags()->sync($post->hashtags->pluck('id'));

        PostLog::record($clone, 'duplicated', 'success', ['original_id' => $post->id]);

        return $clone->load(['platformConfigs', 'hashtags']);
    }

    public function scheduleNow(Post $post, string $scheduledAt, string $timezone): Post
    {
        $utc = Carbon::parse($scheduledAt, $timezone)->utc();

        $post->update([
            'status'       => 'scheduled',
            'scheduled_at' => $utc,
            'timezone'     => $timezone,
        ]);

        $this->syncCalendarEvent($post);
        event(new PostScheduled($post));

        PostLog::record($post, 'scheduled', 'success', ['scheduled_at' => $utc->toIso8601String()]);

        return $post;
    }

    public function cancelSchedule(Post $post): Post
    {
        $post->update(['status' => 'draft', 'scheduled_at' => null]);
        $post->calendarEvent?->update(['status' => 'draft']);

        PostLog::record($post, 'schedule_cancelled', 'success');

        return $post;
    }

    public function getCalendarEvents(int $tenantId, string $start, string $end): array
    {
        return CalendarEvent::where('tenant_id', $tenantId)
            ->whereBetween('start_at', [$start, $end])
            ->with('post')
            ->get()
            ->map(fn ($e) => $e->toCalendarArray())
            ->toArray();
    }

    private function syncPlatformConfigs(Post $post, CreatePostDTO $dto): void
    {
        $existing = $post->platformConfigs->keyBy('platform');
        $touched  = [];

        foreach ($dto->platforms as $platform) {
            $accountUuid = $dto->platformAccounts[$platform] ?? null;
            $overrides   = $dto->platformOverrides[$platform] ?? [];

            $socialAccountId = null;
            if ($accountUuid) {
                $socialAccountId = \App\Models\SocialAccount::where('uuid', $accountUuid)->value('id');
            }

            if ($existing->has($platform)) {
                $existing[$platform]->update([
                    'social_account_id' => $socialAccountId,
                    'content_override'  => $overrides['content'] ?? null,
                    'first_comment'     => $overrides['first_comment'] ?? null,
                    'status'            => 'pending',
                ]);
            } else {
                $post->platformConfigs()->create([
                    'social_account_id' => $socialAccountId,
                    'platform'          => $platform,
                    'content_override'  => $overrides['content'] ?? null,
                    'first_comment'     => $overrides['first_comment'] ?? null,
                    'status'            => 'pending',
                ]);
            }

            $touched[] = $platform;
        }

        // Remove platform configs no longer selected
        $post->platformConfigs()
            ->whereNotIn('platform', $touched)
            ->delete();
    }

    private function syncCalendarEvent(Post $post): void
    {
        if (!$post->scheduled_at) {
            $post->calendarEvent?->delete();
            return;
        }

        $data = [
            'tenant_id' => $post->tenant_id,
            'post_id'   => $post->id,
            'title'     => $post->title ?: Str::limit($post->content, 40),
            'start_at'  => $post->scheduled_at,
            'end_at'    => $post->scheduled_at->addMinutes(30),
            'color'     => $this->platformColor($post->platforms),
            'platforms' => $post->platforms,
            'status'    => $post->status,
            'all_day'   => false,
        ];

        if ($post->calendarEvent) {
            $post->calendarEvent->update($data);
        } else {
            CalendarEvent::create($data);
        }
    }

    private function platformColor(array $platforms): string
    {
        $colors = [
            'facebook'  => '#1877F2',
            'instagram' => '#E1306C',
            'twitter'   => '#1DA1F2',
            'linkedin'  => '#0A66C2',
            'pinterest' => '#E60023',
            'youtube'   => '#FF0000',
            'threads'   => '#000000',
        ];

        $first = $platforms[0] ?? 'facebook';
        return $colors[$first] ?? '#6366F1';
    }
}
