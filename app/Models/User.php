<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'timezone',
        'mfa_enabled',
        'mfa_secret',
        'google_id',
        'microsoft_id',
        'facebook_id',
        'is_super_admin',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'mfa_enabled'       => 'boolean',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
                    ->withPivot(['role', 'permissions', 'invited_at', 'joined_at'])
                    ->withTimestamps();
    }

    public function tenantMemberships(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function assignedMessages(): HasMany
    {
        return $this->hasMany(InboxMessage::class, 'assigned_to');
    }

    public function mediaUploads(): HasMany
    {
        return $this->hasMany(MediaLibrary::class);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function roleInTenant(int $tenantId): ?string
    {
        return $this->tenantMemberships()
                    ->where('tenant_id', $tenantId)
                    ->value('role');
    }

    public function touchLastLogin(): void
    {
        $this->forceFill(['last_login_at' => now()])->save();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function markAllNotificationsRead(): void
    {
        $this->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }
}
