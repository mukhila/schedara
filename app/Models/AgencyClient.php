<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AgencyClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'agency_id',
        'client_name',
        'company_name',
        'email',
        'phone',
        'website',
        'industry',
        'logo',
        'timezone',
        'status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ─────────────────────────────────────────────

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'agency_id');
    }

    public function workspaces(): HasMany
    {
        return $this->hasMany(ClientWorkspace::class);
    }

    public function workspace(): HasOne
    {
        return $this->hasOne(ClientWorkspace::class)->latestOfMany();
    }

    public function onboardingSteps(): HasMany
    {
        return $this->hasMany(ClientOnboarding::class)->orderBy('step_order');
    }

    public function billing(): HasMany
    {
        return $this->hasMany(ClientBilling::class)->latest();
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnboarding(): bool
    {
        return $this->status === 'onboarding';
    }

    public function onboardingProgress(): int
    {
        $total     = $this->onboardingSteps()->count();
        $completed = $this->onboardingSteps()->where('status', 'completed')->count();

        return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
