<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAnalytic extends Model
{
    use HasFactory;
    protected $fillable = [
        'tenant_id', 'post_id', 'social_account_id', 'platform', 'platform_post_id',
        'likes', 'comments', 'shares', 'reach', 'impressions', 'clicks',
        'saves', 'video_views', 'conversions', 'engagement_rate', 'ctr',
        'spend', 'revenue', 'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'likes'           => 'integer',
            'comments'        => 'integer',
            'shares'          => 'integer',
            'reach'           => 'integer',
            'impressions'     => 'integer',
            'clicks'          => 'integer',
            'saves'           => 'integer',
            'video_views'     => 'integer',
            'conversions'     => 'integer',
            'engagement_rate' => 'float',
            'ctr'             => 'float',
            'spend'           => 'float',
            'revenue'         => 'float',
            'fetched_at'      => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo    { return $this->belongsTo(Tenant::class); }
    public function post(): BelongsTo      { return $this->belongsTo(Post::class); }
    public function socialAccount(): BelongsTo { return $this->belongsTo(SocialAccount::class); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }
    public function scopeForPlatform($q, string $p)   { return $q->where('platform', $p); }
    public function scopeInRange($q, string $from, string $to)
    {
        return $q->whereBetween('fetched_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function computedEngagementRate(): float
    {
        if ($this->reach === 0) {
            return 0.0;
        }

        return round(($this->likes + $this->comments + $this->shares + $this->saves) / $this->reach * 100, 4);
    }

    public function roas(): float
    {
        return $this->spend > 0 ? round($this->revenue / $this->spend, 4) : 0.0;
    }
}
