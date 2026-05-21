<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AnalyticsAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'analytics_accounts';

    protected $fillable = [
        'uuid', 'tenant_id', 'social_account_id', 'platform',
        'account_name', 'platform_account_id', 'is_active', 'last_synced_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'last_synced_at' => 'datetime',
            'meta'           => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo        { return $this->belongsTo(Tenant::class); }
    public function socialAccount(): BelongsTo { return $this->belongsTo(SocialAccount::class); }
    public function metrics(): HasMany         { return $this->hasMany(AnalyticsMetric::class); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }
    public function scopeActive($q)                    { return $q->where('is_active', true); }
    public function scopeForPlatform($q, string $p)   { return $q->where('platform', $p); }

    // ── Helpers ──────────────────────────────────────────────────

    public function getRouteKeyName(): string { return 'uuid'; }

    public function markSynced(): void
    {
        $this->update(['last_synced_at' => now()]);
    }
}
