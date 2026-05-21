<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SocialAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'user_id', 'tenant_id', 'platform_id', 'platform_user_id',
        'account_name', 'username', 'email', 'avatar',
        'access_token', 'refresh_token', 'token_expires_at',
        'scopes', 'metadata', 'status', 'last_synced_at',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected function casts(): array
    {
        return [
            'access_token'     => 'encrypted',
            'refresh_token'    => 'encrypted',
            'token_expires_at' => 'datetime',
            'last_synced_at'   => 'datetime',
            'scopes'           => 'array',
            'metadata'         => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($model) => $model->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(SocialPlatform::class, 'platform_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(SocialPage::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SocialLog::class);
    }

    public function postAnalytics(): HasMany
    {
        return $this->hasMany(PostAnalytic::class);
    }

    public function accountAnalytics(): HasMany
    {
        return $this->hasMany(AccountAnalytic::class);
    }

    public function inboxMessages(): HasMany
    {
        return $this->hasMany(InboxMessage::class);
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function isExpiringSoon(int $withinHours = 24): bool
    {
        return $this->token_expires_at
            && $this->token_expires_at->isFuture()
            && $this->token_expires_at->diffInHours(now()) <= $withinHours;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }

    public function isHealthy(): bool
    {
        return $this->isActive();
    }

    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function markRevoked(): void
    {
        $this->update(['status' => 'revoked']);
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeNeedsRefresh($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
              ->orWhere(fn ($q2) => $q2->where('token_expires_at', '<', now()->addHour())
                  ->where('status', 'active'));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
