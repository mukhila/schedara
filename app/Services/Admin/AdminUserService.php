<?php

namespace App\Services\Admin;

use App\Events\Admin\UserSuspended;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminUserService
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = User::with('tenants')->withCount('tenants');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'suspended' => $query->whereNotNull('suspended_at'),
                'active'    => $query->whereNull('suspended_at'),
                'admin'     => $query->where('is_super_admin', true),
                default     => null,
            };
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function suspend(User $user, string $reason = ''): void
    {
        $user->update(['suspended_at' => now()]);

        AdminActivityLog::record('suspend', 'users', "Suspended user {$user->email}. Reason: {$reason}", $user);

        event(new UserSuspended($user, $reason));
    }

    public function activate(User $user): void
    {
        $user->update(['suspended_at' => null]);

        AdminActivityLog::record('activate', 'users', "Activated user {$user->email}", $user);
    }

    public function resetPassword(User $user, string $newPassword): void
    {
        $user->update(['password' => Hash::make($newPassword)]);

        AdminActivityLog::record('reset_password', 'users', "Reset password for user {$user->email}", $user);
    }

    public function makeAdmin(User $user): void
    {
        $user->update(['is_super_admin' => true]);

        AdminActivityLog::record('grant_admin', 'users', "Granted super-admin to {$user->email}", $user);
    }

    public function revokeAdmin(User $user): void
    {
        $user->update(['is_super_admin' => false]);

        AdminActivityLog::record('revoke_admin', 'users', "Revoked super-admin from {$user->email}", $user);
    }

    public function impersonate(User $target): void
    {
        Session::put('admin.impersonating', Auth::id());
        Auth::login($target);

        AdminActivityLog::record('impersonate', 'users', "Started impersonating user {$target->email}", $target);
    }

    public function stopImpersonating(): void
    {
        $adminId = Session::pull('admin.impersonating');
        if ($adminId) {
            Auth::loginUsingId($adminId);
        }
    }

    public function isImpersonating(): bool
    {
        return Session::has('admin.impersonating');
    }
}
