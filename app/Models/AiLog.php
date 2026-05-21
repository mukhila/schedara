<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLog extends Model
{
    protected $fillable = [
        'ai_request_id', 'action', 'ai_provider', 'ai_model',
        'response', 'status', 'tokens_used', 'duration_ms', 'error_message',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function request(): BelongsTo
    {
        return $this->belongsTo(AiRequest::class, 'ai_request_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public static function record(
        int     $requestId,
        string  $action,
        string  $status,
        ?string $provider    = null,
        ?string $model       = null,
        ?string $response    = null,
        int     $tokens      = 0,
        ?int    $durationMs  = null,
        ?string $error       = null,
    ): self {
        return static::create([
            'ai_request_id' => $requestId,
            'action'        => $action,
            'ai_provider'   => $provider,
            'ai_model'      => $model,
            'response'      => $response,
            'status'        => $status,
            'tokens_used'   => $tokens,
            'duration_ms'   => $durationMs,
            'error_message' => $error,
        ]);
    }
}
