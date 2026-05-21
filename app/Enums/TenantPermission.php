<?php

namespace App\Enums;

enum TenantPermission: string
{
    // Tenant-level
    case TenantManage  = 'tenant.manage';
    case TenantBilling = 'tenant.billing';

    // Team
    case TeamView    = 'team.view';
    case TeamInvite  = 'team.invite';
    case TeamManage  = 'team.manage';
    case TeamRemove  = 'team.remove';

    // Posts
    case PostCreate  = 'post.create';
    case PostEdit    = 'post.edit';
    case PostDelete  = 'post.delete';
    case PostPublish = 'post.publish';
    case PostApprove = 'post.approve';

    // Analytics
    case AnalyticsView = 'analytics.view';

    // Inbox
    case InboxView  = 'inbox.view';
    case InboxReply = 'inbox.reply';

    // Media
    case MediaUpload = 'media.upload';
    case MediaDelete = 'media.delete';

    // Settings
    case SettingsView = 'settings.view';
    case SettingsEdit = 'settings.edit';

    public function label(): string
    {
        return match ($this) {
            self::TenantManage  => 'Manage Workspace',
            self::TenantBilling => 'Billing',
            self::TeamView      => 'View Team',
            self::TeamInvite    => 'Invite Members',
            self::TeamManage    => 'Manage Roles',
            self::TeamRemove    => 'Remove Members',
            self::PostCreate    => 'Create Posts',
            self::PostEdit      => 'Edit Posts',
            self::PostDelete    => 'Delete Posts',
            self::PostPublish   => 'Publish Posts',
            self::PostApprove   => 'Approve Posts',
            self::AnalyticsView => 'View Analytics',
            self::InboxView     => 'View Inbox',
            self::InboxReply    => 'Reply in Inbox',
            self::MediaUpload   => 'Upload Media',
            self::MediaDelete   => 'Delete Media',
            self::SettingsView  => 'View Settings',
            self::SettingsEdit  => 'Edit Settings',
        };
    }
}
