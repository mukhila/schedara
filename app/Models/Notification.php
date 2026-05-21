<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'tenant_id',
        'type',
        'category',
        'channel',
        'status',
        'priority',
        'action_url',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'read_at' => 'datetime',
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

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function title(): string
    {
        return data_get($this->data, 'title', '');
    }

    public function body(): string
    {
        return data_get($this->data, 'body', data_get($this->data, 'message', ''));
    }

    public function message(): string
    {
        return $this->body();
    }

    public function categoryIcon(): string
    {
        return match ($this->category) {
            'post'      => 'edit',
            'media'     => 'image',
            'analytics' => 'bar-chart-2',
            'social'    => 'share-2',
            'team'      => 'users',
            'billing'   => 'credit-card',
            default     => 'bell',
        };
    }

    public function priorityColor(): string
    {
        return match ($this->priority) {
            'high'     => 'gold',
            'critical' => 'coral',
            default    => 'brand',
        };
    }
}
