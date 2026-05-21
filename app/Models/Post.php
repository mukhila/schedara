<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    // ── Status constants ─────────────────────────────────────────
    const STATUS_DRAFT            = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED         = 'approved';
    const STATUS_SCHEDULED        = 'scheduled';
    const STATUS_PUBLISHING       = 'publishing';
    const STATUS_PUBLISHED        = 'published';
    const STATUS_FAILED           = 'failed';
    const STATUS_CANCELLED        = 'cancelled';

    // ── Type constants ───────────────────────────────────────────
    const TYPE_TEXT     = 'text';
    const TYPE_IMAGE    = 'image';
    const TYPE_VIDEO    = 'video';
    const TYPE_CAROUSEL = 'carousel';
    const TYPE_REEL     = 'reel';
    const TYPE_SHORTS   = 'shorts';

    protected $fillable = [
        'uuid', 'tenant_id', 'user_id', 'title', 'content', 'caption',
        'type', 'media_urls', 'platforms', 'status',
        'scheduled_at', 'published_at', 'timezone',
        'is_evergreen', 'auto_repost', 'repost_frequency', 'next_repost_at',
        'best_time_score', 'ai_metadata', 'failure_reason', 'post_ids',
    ];

    protected function casts(): array
    {
        return [
            'media_urls'     => 'array',
            'platforms'      => 'array',
            'post_ids'       => 'array',
            'ai_metadata'    => 'array',
            'scheduled_at'   => 'datetime',
            'published_at'   => 'datetime',
            'next_repost_at' => 'datetime',
            'is_evergreen'   => 'boolean',
            'auto_repost'    => 'boolean',
            'best_time_score'=> 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo          { return $this->belongsTo(User::class); }
    public function platformConfigs(): HasMany  { return $this->hasMany(PostPlatformConfig::class); }
    public function media(): HasMany            { return $this->hasMany(PostMedia::class)->orderBy('sort_order'); }
    public function analytics(): HasMany        { return $this->hasMany(PostAnalytic::class); }
    public function logs(): HasMany             { return $this->hasMany(PostLog::class)->latest(); }
    public function calendarEvent(): HasOne     { return $this->hasOne(CalendarEvent::class); }
    public function approvalWorkflow(): HasOne  { return $this->hasOne(ApprovalWorkflow::class); }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags');
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeScheduled($q)    { return $q->where('status', self::STATUS_SCHEDULED)->whereNotNull('scheduled_at'); }
    public function scopePublished($q)    { return $q->where('status', self::STATUS_PUBLISHED); }
    public function scopeDrafts($q)       { return $q->where('status', self::STATUS_DRAFT); }
    public function scopeEvergreen($q)    { return $q->where('is_evergreen', true)->where('auto_repost', true); }
    public function scopeDue($q)          { return $q->scheduled()->where('scheduled_at', '<=', now()); }
    public function scopeDueRepost($q)    { return $q->evergreen()->where('next_repost_at', '<=', now()); }

    // ── Helpers ──────────────────────────────────────────────────

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_APPROVED, self::STATUS_SCHEDULED]);
    }

    public function effectiveCaption(): string
    {
        return $this->caption ?: ($this->content ?? '');
    }

    public function markFailed(string $reason): void
    {
        $this->update(['status' => self::STATUS_FAILED, 'failure_reason' => $reason]);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED        => '#22B07E',
            self::STATUS_SCHEDULED        => '#65a1d8',
            self::STATUS_FAILED           => '#FF401C',
            self::STATUS_PUBLISHING       => '#FDBB1F',
            self::STATUS_DRAFT            => '#94a3b8',
            self::STATUS_PENDING_APPROVAL => '#a78bfa',
            default                       => '#94a3b8',
        };
    }

    public function getRouteKeyName(): string { return 'uuid'; }
}
