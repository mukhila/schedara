<?php

namespace App\Models;

use App\Enums\TenantPermission;
use App\Enums\TenantRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUser extends Model
{
    protected $table = 'tenant_users';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'permissions',
        'invited_at',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'invited_at'  => 'datetime',
            'joined_at'   => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Role helpers ──────────────────────────────────────────────

    public function roleEnum(): TenantRole
    {
        return TenantRole::from($this->role);
    }

    public function hasPermission(string|TenantPermission $permission): bool
    {
        $perm = $permission instanceof TenantPermission
            ? $permission
            : TenantPermission::from($permission);

        // Explicit per-user overrides (stored as array of permission values)
        if (!empty($this->permissions)) {
            return in_array($perm->value, $this->permissions, strict: true);
        }

        return $this->roleEnum()->can($perm);
    }

    public function isOwner(): bool   { return $this->role === TenantRole::Owner->value; }
    public function isAdmin(): bool   { return in_array($this->role, [TenantRole::Owner->value, TenantRole::Admin->value]); }
    public function isManager(): bool { return in_array($this->role, [TenantRole::Owner->value, TenantRole::Admin->value, TenantRole::Manager->value]); }

    public function accept(): void
    {
        $this->update(['joined_at' => now()]);
    }
}
