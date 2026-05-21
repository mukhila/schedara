<?php

namespace App\Providers;

use App\Events\Admin\ApiQuotaExceeded;
use App\Events\Admin\TicketCreated;
use App\Events\Admin\UserSuspended;
use App\Events\Collaboration\CommentAdded;
use App\Events\Collaboration\PostApprovalRequested;
use App\Events\Collaboration\PostApproved;
use App\Events\Collaboration\PostRejected;
use App\Events\Collaboration\TaskAssigned;
use App\Events\Collaboration\TaskCompleted;
use App\Events\Notifications\InAppNotificationCreated;
use App\Listeners\Admin\LogAdminActivity;
use App\Listeners\Admin\SendTicketNotifications;
use App\Listeners\Collaboration\SendCollaborationNotification;
use App\Listeners\Collaboration\WriteActivityLog;
use App\Listeners\Notifications\HandleInAppNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserSuspended::class => [
            [LogAdminActivity::class, 'handleUserSuspended'],
        ],
        TicketCreated::class => [
            [LogAdminActivity::class,       'handleTicketCreated'],
            [SendTicketNotifications::class, 'handle'],
        ],
        ApiQuotaExceeded::class => [
            [LogAdminActivity::class, 'handleApiQuotaExceeded'],
        ],
        TaskAssigned::class => [
            [SendCollaborationNotification::class, 'handleTaskAssigned'],
            [WriteActivityLog::class,              'handleTaskAssigned'],
        ],
        TaskCompleted::class => [
            [SendCollaborationNotification::class, 'handleTaskCompleted'],
            [WriteActivityLog::class,              'handleTaskCompleted'],
        ],
        PostApprovalRequested::class => [
            [SendCollaborationNotification::class, 'handleApprovalRequested'],
            [WriteActivityLog::class,              'handleApprovalRequested'],
        ],
        PostApproved::class => [
            [SendCollaborationNotification::class, 'handlePostApproved'],
            [WriteActivityLog::class,              'handlePostApproved'],
        ],
        PostRejected::class => [
            [SendCollaborationNotification::class, 'handlePostRejected'],
            [WriteActivityLog::class,              'handlePostRejected'],
        ],
        CommentAdded::class => [
            [SendCollaborationNotification::class, 'handleCommentAdded'],
            [WriteActivityLog::class,              'handleCommentAdded'],
        ],
        InAppNotificationCreated::class => [
            HandleInAppNotification::class,
        ],
    ];
}
