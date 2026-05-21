<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ClientWorkspace extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'agency_client_id',
        'workspace_name',
        'domain',
        'branding_settings',
        'settings',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branding_settings' => 'array',
            'settings'          => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ─────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(AgencyClient::class, 'agency_client_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ClientReport::class)->latest();
    }

    public function whiteLabelSettings(): HasOne
    {
        return $this->hasOne(WhiteLabelSetting::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ClientActivityLog::class)->latest();
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function getBranding(string $key, mixed $default = null): mixed
    {
        return data_get($this->branding_settings, $key, $default);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
