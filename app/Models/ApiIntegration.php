<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ApiIntegration extends Model
{
    protected $fillable = [
        'uuid', 'provider_name', 'display_name',
        'api_key', 'api_secret', 'environment',
        'status', 'usage_limit', 'current_usage',
        'monthly_cost_cents', 'last_checked_at', 'last_error', 'metadata',
    ];

    protected $hidden = ['api_key', 'api_secret'];

    protected function casts(): array
    {
        return [
            'metadata'       => 'array',
            'last_checked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string { return 'uuid'; }

    // ── Encrypted key accessors ──────────────────────────────────

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiKeyAttribute(?string $value): ?string
    {
        if (! $value) return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }

    public function setApiSecretAttribute(?string $value): void
    {
        $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiSecretAttribute(?string $value): ?string
    {
        if (! $value) return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function maskedKey(): string
    {
        $key = $this->api_key ?? '';
        if (strlen($key) <= 8) return str_repeat('*', max(strlen($key), 8));
        return substr($key, 0, 4) . str_repeat('*', max(strlen($key) - 8, 4)) . substr($key, -4);
    }

    public function isActive(): bool { return $this->status === 'active'; }
    public function hasError(): bool { return $this->status === 'error'; }

    public function usagePercent(): float
    {
        if (! $this->usage_limit || $this->usage_limit === 0) return 0.0;
        return min(100, round(($this->current_usage / $this->usage_limit) * 100, 1));
    }

    public function isNearLimit(int $threshold = 80): bool
    {
        return $this->usage_limit && $this->usagePercent() >= $threshold;
    }
}
