<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsDemographic extends Model
{
    use HasFactory;

    protected $table = 'analytics_demographics';

    protected $fillable = [
        'tenant_id', 'social_account_id', 'platform', 'date',
        'dimension', 'dimension_value', 'count', 'percentage',
    ];

    protected function casts(): array
    {
        return [
            'date'       => 'date',
            'count'      => 'integer',
            'percentage' => 'float',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo        { return $this->belongsTo(Tenant::class); }
    public function socialAccount(): BelongsTo { return $this->belongsTo(SocialAccount::class); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeForTenant($q, int $tenantId)    { return $q->where('tenant_id', $tenantId); }
    public function scopeDimension($q, string $dimension) { return $q->where('dimension', $dimension); }
    public function scopeInRange($q, string $from, string $to)
    {
        return $q->whereBetween('date', [$from, $to]);
    }
}
