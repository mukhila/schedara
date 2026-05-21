<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'user_id', 'device_type', 'fcm_token',
        'browser', 'platform', 'last_active_at',
    ];

    protected function casts(): array
    {
        return ['last_active_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function touch(): bool
    {
        return $this->update(['last_active_at' => now()]);
    }
}
