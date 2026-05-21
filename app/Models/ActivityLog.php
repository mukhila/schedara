<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id', 'user_id', 'action', 'module',
        'subject_type', 'subject_id', 'description', 'properties',
        'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helper ─────────────────────────────────────────────

    public static function record(
        string  $action,
        string  $module,
        ?string $description = null,
        array   $properties  = [],
        ?int    $tenantId    = null,
        ?int    $userId      = null,
        ?object $subject     = null,
    ): self {
        return static::create([
            'tenant_id'    => $tenantId ?? (app()->bound('current.tenant') ? app('current.tenant')?->id : null),
            'user_id'      => $userId   ?? auth()->id(),
            'action'       => $action,
            'module'       => $module,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id'   => $subject?->id,
            'description'  => $description,
            'properties'   => $properties ?: null,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->where('created_at', '<=', $to . ' 23:59:59'));
    }
}
