<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsLog extends Model
{
    protected $table = 'analytics_logs';

    protected $fillable = [
        'tenant_id', 'action', 'platform', 'status',
        'response', 'error_message', 'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'response'    => 'array',
            'duration_ms' => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }
    public function scopeErrors($q)                    { return $q->where('status', 'error'); }
    public function scopeRecent($q, int $days = 7)     { return $q->where('created_at', '>=', now()->subDays($days)); }

    // ── Static helpers ───────────────────────────────────────────

    public static function record(
        int $tenantId,
        string $action,
        string $status,
        array $response = [],
        ?string $platform = null,
        ?string $errorMessage = null,
        ?int $durationMs = null,
    ): self {
        return static::create([
            'tenant_id'     => $tenantId,
            'action'        => $action,
            'platform'      => $platform,
            'status'        => $status,
            'response'      => $response ?: null,
            'error_message' => $errorMessage,
            'duration_ms'   => $durationMs,
        ]);
    }
}
