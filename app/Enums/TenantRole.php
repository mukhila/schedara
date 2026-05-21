<?php

namespace App\Enums;

enum TenantRole: string
{
    case Owner   = 'owner';
    case Admin   = 'admin';
    case Manager = 'manager';
    case Creator = 'creator';
    case Analyst = 'analyst';
    case Client  = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Owner   => 'Owner',
            self::Admin   => 'Admin',
            self::Manager => 'Manager',
            self::Creator => 'Creator',
            self::Analyst => 'Analyst',
            self::Client  => 'Client',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Owner   => '#f59e0b',
            self::Admin   => '#65a1d8',
            self::Manager => '#8b5cf6',
            self::Creator => '#10b981',
            self::Analyst => '#6b7280',
            self::Client  => '#9ca3af',
        };
    }

    /** All permissions this role holds. */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner, self::Admin => TenantPermission::cases(),

            self::Manager => [
                TenantPermission::TeamView,
                TenantPermission::TeamInvite,
                TenantPermission::PostCreate,
                TenantPermission::PostEdit,
                TenantPermission::PostDelete,
                TenantPermission::PostPublish,
                TenantPermission::PostApprove,
                TenantPermission::AnalyticsView,
                TenantPermission::InboxView,
                TenantPermission::InboxReply,
                TenantPermission::MediaUpload,
                TenantPermission::MediaDelete,
                TenantPermission::SettingsView,
            ],

            self::Creator => [
                TenantPermission::TeamView,
                TenantPermission::PostCreate,
                TenantPermission::PostEdit,
                TenantPermission::PostPublish,
                TenantPermission::AnalyticsView,
                TenantPermission::InboxView,
                TenantPermission::InboxReply,
                TenantPermission::MediaUpload,
            ],

            self::Analyst => [
                TenantPermission::TeamView,
                TenantPermission::AnalyticsView,
            ],

            self::Client => [
                TenantPermission::AnalyticsView,
            ],
        };
    }

    public function can(TenantPermission $permission): bool
    {
        return in_array($permission, $this->permissions(), strict: true);
    }

    /** Roles this role may assign when inviting. */
    public function assignableRoles(): array
    {
        return match ($this) {
            self::Owner   => [self::Admin, self::Manager, self::Creator, self::Analyst, self::Client],
            self::Admin   => [self::Manager, self::Creator, self::Analyst, self::Client],
            self::Manager => [self::Creator, self::Analyst, self::Client],
            default       => [],
        };
    }

    public static function selectOptions(): array
    {
        return collect(self::cases())
            ->filter(fn ($r) => $r !== self::Owner)
            ->mapWithKeys(fn ($r) => [$r->value => $r->label()])
            ->all();
    }
}
