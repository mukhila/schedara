<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AiConversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'user_id', 'ai_provider', 'ai_model',
        'title', 'system_prompt', 'message_count', 'last_message_at',
    ];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

    public function messages(): HasMany
    {
        return $this->hasMany(AiConversationMessage::class, 'conversation_id')->orderBy('created_at');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId, int $tenantId)
    {
        return $query->where('user_id', $userId)->where('tenant_id', $tenantId);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function autoTitle(): string
    {
        if ($this->title) return $this->title;
        $first = $this->messages()->where('role', 'user')->first();
        return $first ? Str::limit($first->content, 50) : 'New conversation';
    }

    public function contextMessages(): array
    {
        return $this->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();
    }
}
