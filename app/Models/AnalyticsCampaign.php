<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AnalyticsCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'analytics_campaigns';

    protected $fillable = [
        'uuid', 'tenant_id', 'created_by', 'name', 'status', 'platform',
        'start_date', 'end_date', 'budget', 'spend', 'revenue',
        'impressions', 'clicks', 'conversions', 'reach', 'engagement',
        'ctr', 'cpc', 'cpm', 'roas', 'roi', 'tags', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'start_date'  => 'date',
            'end_date'    => 'date',
            'budget'      => 'float',
            'spend'       => 'float',
            'revenue'     => 'float',
            'impressions' => 'integer',
            'clicks'      => 'integer',
            'conversions' => 'integer',
            'reach'       => 'integer',
            'engagement'  => 'integer',
            'ctr'         => 'float',
            'cpc'         => 'float',
            'cpm'         => 'float',
            'roas'        => 'float',
            'roi'         => 'float',
            'tags'        => 'array',
            'meta'        => 'array',
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
    public function scopeActive($q)                    { return $q->where('status', 'active'); }
    public function scopeCompleted($q)                 { return $q->where('status', 'completed'); }
    public function scopeInRange($q, string $from, string $to)
    {
        return $q->where('start_date', '<=', $to)->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $from));
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function getRouteKeyName(): string { return 'uuid'; }

    public function budgetUtilization(): float
    {
        return $this->budget > 0 ? round($this->spend / $this->budget * 100, 2) : 0.0;
    }

    public function conversionRate(): float
    {
        return $this->clicks > 0 ? round($this->conversions / $this->clicks * 100, 4) : 0.0;
    }
}
