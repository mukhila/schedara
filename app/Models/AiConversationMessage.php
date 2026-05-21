<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiConversationMessage extends Model
{
    protected $fillable = [
        'uuid', 'conversation_id', 'role', 'content', 'tokens_used', 'ai_request_id',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());

        static::created(function (self $message) {
            $message->conversation->increment('message_count');
            $message->conversation->update(['last_message_at' => now()]);
        });
    }

    // ── Relationships ─────────────────────────────────────────────

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function aiRequest(): BelongsTo
    {
        return $this->belongsTo(AiRequest::class);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isUser(): bool      { return $this->role === 'user'; }
    public function isAssistant(): bool { return $this->role === 'assistant'; }
}
