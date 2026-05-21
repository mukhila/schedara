<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AnalyticsReport extends Model
{
    use HasFactory;

    protected $table = 'analytics_reports';

    protected $fillable = [
        'uuid', 'tenant_id', 'created_by', 'name', 'type', 'status',
        'date_from', 'date_to', 'filters', 'metrics', 'format',
        'file_path', 'file_url', 'generated_at', 'expires_at', 'summary',
    ];

    protected function casts(): array
    {
        return [
            'date_from'    => 'date',
            'date_to'      => 'date',
            'filters'      => 'array',
            'metrics'      => 'array',
            'summary'      => 'array',
            'generated_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }
    public function scopeReady($q)                     { return $q->where('status', 'ready'); }

    // ── Helpers ──────────────────────────────────────────────────

    public function getRouteKeyName(): string { return 'uuid'; }

    public function isReady(): bool    { return $this->status === 'ready'; }
    public function isExpired(): bool  { return $this->expires_at && $this->expires_at->isPast(); }
}
