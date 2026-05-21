<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardLayout extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'name',
        'widgets_order',
        'widgets_hidden',
        'widgets_config',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'widgets_order'  => 'array',
            'widgets_hidden' => 'array',
            'widgets_config' => 'array',
            'is_default'     => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function orderedWidgets(): array
    {
        return $this->widgets_order ?? self::defaultOrder();
    }

    public function hiddenWidgets(): array
    {
        return $this->widgets_hidden ?? [];
    }

    public function isHidden(string $key): bool
    {
        return in_array($key, $this->hiddenWidgets(), true);
    }

    public function widgetConfig(string $key): array
    {
        return $this->widgets_config[$key] ?? [];
    }

    // ── Statics ──────────────────────────────────────────────────

    public static function defaultOrder(): array
    {
        return [
            'kpi-cards',
            'engagement',
            'followers',
            'post-performance',
            'platform-comparison',
            'revenue',
            'ai-insights',
        ];
    }

    public static function forUser(int $userId, ?int $tenantId, string $name = 'default'): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'tenant_id' => $tenantId, 'name' => $name],
            ['widgets_order' => static::defaultOrder(), 'widgets_hidden' => [], 'is_default' => true]
        );
    }
}
