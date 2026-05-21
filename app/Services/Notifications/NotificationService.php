<?php

namespace App\Services\Notifications;

use App\Events\Notifications\InAppNotificationCreated;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    public function __construct(
        private readonly NotificationChannelRouter $channelRouter,
    ) {}

    /**
     * Create and dispatch a notification for a user across all enabled channels.
     *
     * @param  object|null  $mailNotification  Laravel Notification for mail; must return ['mail'] from via().
     */
    public function send(
        User    $user,
        string  $type,
        string  $category,
        string  $title,
        string  $body,
        array   $payload    = [],
        ?string $actionUrl  = null,
        string  $priority   = 'normal',
        ?int    $tenantId   = null,
        ?object $mailNotification = null,
    ): ?Notification {
        // 1. Store in-app (database) if the user has this channel enabled
        $notification = null;

        if (NotificationPreference::isEnabled($user->id, $category, 'database')) {
            $notification = Notification::create([
                'user_id'    => $user->id,
                'tenant_id'  => $tenantId,
                'type'       => $type,
                'category'   => $category,
                'priority'   => $priority,
                'action_url' => $actionUrl,
                'data'       => array_merge($payload, ['title' => $title, 'body' => $body]),
            ]);

            // Bust the cached unread count
            Cache::forget("notif_unread_{$user->id}");

            // 2. Broadcast real-time if channel enabled
            if (NotificationPreference::isEnabled($user->id, $category, 'broadcast')) {
                event(new InAppNotificationCreated($notification));
            }

            // 3. Dispatch outbound channel jobs (push, whatsapp, sms, slack)
            $this->channelRouter->dispatchForUser($user, $notification);
        }

        // 4. Mail if the user has mail enabled for this category
        if ($mailNotification && NotificationPreference::isEnabled($user->id, $category, 'mail')) {
            $user->notify($mailNotification);
        }

        return $notification;
    }

    /**
     * Send the same notification to every active member of a tenant.
     */
    public function sendToTenant(
        int    $tenantId,
        string $type,
        string $category,
        string $title,
        string $body,
        array  $payload   = [],
        ?string $actionUrl = null,
        string $priority  = 'normal',
    ): void {
        User::whereHas('tenants', fn ($q) => $q->where('tenants.id', $tenantId))
            ->get()
            ->each(fn (User $user) => $this->send(
                $user, $type, $category, $title, $body, $payload, $actionUrl, $priority, $tenantId
            ));
    }

    // ── Queries ──────────────────────────────────────────────────

    public function forUser(
        int    $userId,
        int    $perPage  = 20,
        ?string $category = null,
        ?string $filter   = null,  // 'unread' | 'read' | null
    ): LengthAwarePaginator {
        return Notification::forUser($userId)
            ->when($category, fn ($q) => $q->ofCategory($category))
            ->when($filter === 'unread', fn ($q) => $q->unread())
            ->when($filter === 'read',   fn ($q) => $q->read())
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function recent(int $userId, int $limit = 8): Collection
    {
        return Notification::forUser($userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function unreadCount(int $userId): int
    {
        return (int) Cache::remember("notif_unread_{$userId}", 60, fn () =>
            Notification::forUser($userId)->unread()->count()
        );
    }

    // ── Mutations ────────────────────────────────────────────────

    public function markRead(int $userId, int $notificationId): void
    {
        Notification::forUser($userId)->where('id', $notificationId)->update(['read_at' => now()]);
        Cache::forget("notif_unread_{$userId}");
    }

    public function markAllRead(int $userId): void
    {
        Notification::forUser($userId)->unread()->update(['read_at' => now()]);
        Cache::forget("notif_unread_{$userId}");
    }

    public function delete(int $userId, int $notificationId): void
    {
        Notification::forUser($userId)->where('id', $notificationId)->delete();
        Cache::forget("notif_unread_{$userId}");
    }

    public function clearAll(int $userId): void
    {
        Notification::forUser($userId)->delete();
        Cache::forget("notif_unread_{$userId}");
    }

    // ── Preferences ──────────────────────────────────────────────

    public function getPreferences(int $userId): array
    {
        return NotificationPreference::forUser($userId);
    }

    public function updatePreferences(int $userId, array $preferences): void
    {
        foreach ($preferences as $category => $channels) {
            foreach ($channels as $channel => $enabled) {
                NotificationPreference::setForUser($userId, $category, $channel, (bool) $enabled);
            }
        }
    }
}
