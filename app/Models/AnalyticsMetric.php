<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsMetric extends Model
{
    use HasFactory;

    protected $table = 'analytics_metrics';

    protected $fillable = [
        'analytics_account_id', 'tenant_id', 'metric_date',
        'impressions', 'reach_count',
        'engagement_count', 'likes', 'comments', 'shares', 'saves', 'replies', 'mentions', 'reactions',
        'clicks', 'conversions', 'profile_visits', 'website_clicks',
        'followers', 'unfollows', 'new_followers',
        'revenue', 'spend', 'engagement_rate',
        'organic_reach', 'paid_reach', 'viral_reach',
    ];

    protected function casts(): array
    {
        return [
            'metric_date'      => 'date',
            'impressions'      => 'integer',
            'reach_count'      => 'integer',
            'engagement_count' => 'integer',
            'likes'            => 'integer',
            'comments'         => 'integer',
            'shares'           => 'integer',
            'saves'            => 'integer',
            'replies'          => 'integer',
            'mentions'         => 'integer',
            'reactions'        => 'integer',
            'clicks'           => 'integer',
            'conversions'      => 'integer',
            'profile_visits'   => 'integer',
            'website_clicks'   => 'integer',
            'followers'        => 'integer',
            'unfollows'        => 'integer',
            'new_followers'    => 'integer',
            'revenue'          => 'float',
            'spend'            => 'float',
            'engagement_rate'  => 'float',
            'organic_reach'    => 'integer',
            'paid_reach'       => 'integer',
            'viral_reach'      => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo          { return $this->belongsTo(Tenant::class); }
    public function analyticsAccount(): BelongsTo { return $this->belongsTo(AnalyticsAccount::class); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }
    public function scopeInRange($q, string $from, string $to)
    {
        return $q->whereBetween('metric_date', [$from, $to]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function computeEngagementRate(): float
    {
        return $this->reach_count > 0
            ? round($this->engagement_count / $this->reach_count * 100, 4)
            : 0.0;
    }

    public function netFollowerGrowth(): int
    {
        return $this->new_followers - $this->unfollows;
    }

    public function roi(): float
    {
        return $this->spend > 0
            ? round(($this->revenue - $this->spend) / $this->spend * 100, 4)
            : 0.0;
    }
}
