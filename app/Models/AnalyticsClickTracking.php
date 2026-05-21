<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AnalyticsClickTracking extends Model
{
    use HasFactory;

    protected $table = 'analytics_click_tracking';

    protected $fillable = [
        'uuid', 'tenant_id', 'post_id', 'campaign_id', 'platform',
        'url', 'short_code', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content',
        'clicks', 'unique_clicks', 'conversions', 'revenue',
        'device', 'country', 'referrer', 'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'clicks'        => 'integer',
            'unique_clicks' => 'integer',
            'conversions'   => 'integer',
            'revenue'       => 'float',
            'clicked_at'    => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $m->uuid       ??= (string) Str::uuid();
            $m->short_code ??= Str::random(8);
        });
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo   { return $this->belongsTo(Tenant::class); }
    public function post(): BelongsTo     { return $this->belongsTo(Post::class); }
    public function campaign(): BelongsTo { return $this->belongsTo(AnalyticsCampaign::class, 'campaign_id'); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }
    public function scopeInRange($q, string $from, string $to)
    {
        return $q->whereBetween('clicked_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function conversionRate(): float
    {
        return $this->clicks > 0 ? round($this->conversions / $this->clicks * 100, 4) : 0.0;
    }
}
