<?php

namespace App\Services\Social;

use App\Models\InboxMessage;
use App\Models\SocialAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class InboxService
{
    public function list(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        return InboxMessage::forTenant($tenantId)
            ->when(isset($filters['status']),     fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['platform']),   fn ($q) => $q->where('platform', $filters['platform']))
            ->when(isset($filters['type']),       fn ($q) => $q->where('type', $filters['type']))
            ->when(isset($filters['assigned_to']),fn ($q) => $q->where('assigned_to', $filters['assigned_to']))
            ->with(['socialAccount', 'assignee'])
            ->orderByDesc('received_at')
            ->paginate($filters['per_page'] ?? 25);
    }

    public function unreadCount(int $tenantId): int
    {
        return InboxMessage::forTenant($tenantId)->unread()->count();
    }

    public function markRead(InboxMessage $message): void
    {
        $message->markRead();
    }

    public function archive(InboxMessage $message): void
    {
        $message->update(['status' => 'archived']);
    }

    public function assign(InboxMessage $message, int $userId): void
    {
        $message->assignTo($userId);
    }

    public function reply(InboxMessage $message, string $text): bool
    {
        $account = $message->socialAccount;
        if (! $account) {
            return false;
        }

        $service = $this->resolveService($account->platform?->slug ?? '');
        if (! $service) {
            Log::warning('InboxService: no service for platform', ['platform' => $message->platform]);
            return false;
        }

        try {
            $service->replyToMessage($account, $message->external_id, $text);
            $message->markRead();
            return true;
        } catch (\Throwable $e) {
            Log::error('InboxService reply failed', [
                'message_id' => $message->id,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function syncFromPlatforms(int $tenantId): int
    {
        $synced = 0;

        SocialAccount::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('platform')
            ->chunk(20, function ($accounts) use (&$synced) {
                foreach ($accounts as $account) {
                    $platform = $account->platform?->slug ?? '';
                    $service  = $this->resolveService($platform);

                    if (! $service) {
                        continue;
                    }

                    try {
                        $messages = $service->fetchInbox($account);

                        foreach ($messages as $msg) {
                            InboxMessage::updateOrCreate(
                                [
                                    'social_account_id' => $account->id,
                                    'external_id'       => $msg['external_id'],
                                ],
                                [
                                    'tenant_id'   => $account->tenant_id,
                                    'platform'    => $platform,
                                    'type'        => $msg['type'] ?? 'message',
                                    'from_user'   => $msg['from_user'] ?? [],
                                    'content'     => $msg['content'] ?? '',
                                    'sentiment'   => $msg['sentiment'] ?? null,
                                    'received_at' => $msg['received_at'] ?? now(),
                                    'status'      => $msg['status'] ?? 'unread',
                                ]
                            );
                            $synced++;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('InboxService sync failed', [
                            'account_id' => $account->id,
                            'platform'   => $platform,
                            'error'      => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $synced;
    }

    private function resolveService(string $platform): ?BaseSocialService
    {
        return match ($platform) {
            'facebook'  => new FacebookService(),
            'instagram' => new InstagramService(),
            'twitter'   => new TwitterService(),
            'linkedin'  => new LinkedInService(),
            'tiktok'    => new TikTokService(),
            'youtube'   => new YouTubeService(),
            'threads'   => new ThreadsService(),
            'pinterest' => new PinterestService(),
            default     => null,
        };
    }
}
