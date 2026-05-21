<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AiGeneratedContent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'user_id', 'ai_request_id',
        'content_type', 'platform', 'title', 'generated_content',
        'variations', 'metadata', 'is_saved', 'is_used',
    ];

    protected function casts(): array
    {
        return [
            'variations' => 'array',
            'metadata'   => 'array',
            'is_saved'   => 'boolean',
            'is_used'    => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenant(): BelongsTo    { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function aiRequest(): BelongsTo { return $this->belongsTo(AiRequest::class); }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSaved($query)
    {
        return $query->where('is_saved', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('content_type', $type);
    }
}
