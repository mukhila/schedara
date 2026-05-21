<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLog extends Model
{
    public const UPDATED_AT = null; // logs are immutable

    protected $fillable = [
        'social_account_id', 'action', 'platform',
        'request_data', 'response_data', 'status', 'error_message', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'request_data'  => 'array',
            'response_data' => 'array',
        ];
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    // ── Factory helpers ──────────────────────────────────────────

    public static function record(
        ?SocialAccount $account,
        string $action,
        string $status,
        array $data = [],
        ?string $error = null
    ): self {
        return static::create([
            'social_account_id' => $account?->id,
            'action'            => $action,
            'platform'          => $account?->platform?->slug,
            'response_data'     => $data,
            'status'            => $status,
            'error_message'     => $error,
            'ip_address'        => request()->ip(),
        ]);
    }
}
