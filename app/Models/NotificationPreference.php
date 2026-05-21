<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'category', 'channel', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helpers ───────────────────────────────────────────

    public static function isEnabled(int $userId, string $category, string $channel): bool
    {
        $pref = static::where('user_id', $userId)
            ->where('category', $category)
            ->where('channel', $channel)
            ->first();

        // No preference record = use config default
        return $pref ? $pref->enabled : (bool) config("notifications.defaults.{$channel}", true);
    }

    public static function forUser(int $userId): array
    {
        $rows = static::where('user_id', $userId)->get()
            ->keyBy(fn ($r) => "{$r->category}.{$r->channel}");

        $categories = array_keys(config('notifications.categories', []));
        $channels   = config('notifications.channels', ['database', 'mail', 'broadcast']);
        $defaults   = config('notifications.defaults', []);

        $result = [];
        foreach ($categories as $cat) {
            foreach ($channels as $chan) {
                $key = "{$cat}.{$chan}";
                $result[$cat][$chan] = $rows->has($key)
                    ? $rows[$key]->enabled
                    : ($defaults[$chan] ?? true);
            }
        }

        return $result;
    }

    public static function setForUser(int $userId, string $category, string $channel, bool $enabled): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'category' => $category, 'channel' => $channel],
            ['enabled' => $enabled]
        );
    }
}
