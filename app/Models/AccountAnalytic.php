<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountAnalytic extends Model
{
    use HasFactory;
    protected $fillable = [
        'tenant_id', 'social_account_id', 'date',
        'followers', 'following', 'unfollows', 'posts_count',
        'reach', 'impressions', 'profile_views',
        'likes', 'comments', 'shares', 'clicks', 'website_clicks',
        'engagement_rate', 'revenue',
    ];

    protected function casts(): array
    {
        return [
            'date'            => 'date',
            'followers'       => 'integer',
            'following'       => 'integer',
            'unfollows'       => 'integer',
            'posts_count'     => 'integer',
            'reach'           => 'integer',
            'impressions'     => 'integer',
            'profile_views'   => 'integer',
            'likes'           => 'integer',
            'comments'        => 'integer',
            'shares'          => 'integer',
            'clicks'          => 'integer',
            'website_clicks'  => 'integer',
            'engagement_rate' => 'float',
            'revenue'         => 'float',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo        { return $this->belongsTo(Tenant::class); }
    public function socialAccount(): BelongsTo { return $this->belongsTo(SocialAccount::class); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }

    public function scopeInRange($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeLastDays($query, int $days)
    {
        return $query->where('date', '>=', now()->subDays($days)->toDateString());
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function netFollowerGrowth(): int
    {
        return $this->followers - ($this->unfollows ?? 0);
    }
}
