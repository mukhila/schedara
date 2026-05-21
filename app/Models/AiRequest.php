<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AiRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'user_id', 'ai_provider', 'ai_model',
        'request_type', 'prompt', 'system_prompt', 'response',
        'tokens_input', 'tokens_output', 'tokens_used', 'cost_estimate',
        'processing_time_ms', 'status', 'error_message', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata'      => 'array',
            'cost_estimate' => 'decimal:6',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

    public function generatedContent(): HasMany
    {
        return $this->hasMany(AiGeneratedContent::class);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }

    public function markCompleted(string $response, int $inputTokens, int $outputTokens, float $cost, int $ms): void
    {
        $this->update([
            'response'           => $response,
            'tokens_input'       => $inputTokens,
            'tokens_output'      => $outputTokens,
            'tokens_used'        => $inputTokens + $outputTokens,
            'cost_estimate'      => $cost,
            'processing_time_ms' => $ms,
            'status'             => 'completed',
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update(['status' => 'failed', 'error_message' => $error]);
    }
}
