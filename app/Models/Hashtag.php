<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hashtag extends Model
{
    protected $fillable = [
        'tenant_id', 'hashtag', 'group_name', 'usage_count', 'avg_engagement', 'is_trending',
    ];

    protected function casts(): array
    {
        return [
            'is_trending'     => 'boolean',
            'avg_engagement'  => 'decimal:4',
        ];
    }

    public function tenant(): BelongsTo    { return $this->belongsTo(Tenant::class); }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_hashtags');
    }

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }
    public function scopeGroup($q, string $group)      { return $q->where('group_name', $group); }
    public function scopeTrending($q)                  { return $q->where('is_trending', true); }

    public function withHash(): string { return '#' . $this->hashtag; }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
