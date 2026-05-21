<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AiTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'created_by', 'template_name', 'template_type',
        'description', 'prompt_template', 'variables', 'ai_provider',
        'ai_model', 'is_public', 'is_system', 'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'variables'  => 'array',
            'is_public'  => 'boolean',
            'is_system'  => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenant(): BelongsTo     { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePublicOrOwned($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('template_type', $type);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function render(array $variables): string
    {
        $prompt = $this->prompt_template;
        foreach ($variables as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }
        return $prompt;
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
