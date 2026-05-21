<?php

namespace App\Listeners\Admin;

use App\Events\Admin\ApiQuotaExceeded;
use App\Events\Admin\TicketCreated;
use App\Events\Admin\UserSuspended;
use App\Models\AdminActivityLog;

class LogAdminActivity
{
    public function handleUserSuspended(UserSuspended $event): void
    {
        AdminActivityLog::record(
            'suspend', 'users',
            "User {$event->user->email} suspended. Reason: {$event->reason}",
            $event->user,
        );
    }

    public function handleTicketCreated(TicketCreated $event): void
    {
        AdminActivityLog::record(
            'create', 'tickets',
            "Support ticket {$event->ticket->ticket_number} created: {$event->ticket->subject}",
            $event->ticket,
        );
    }

    public function handleApiQuotaExceeded(ApiQuotaExceeded $event): void
    {
        AdminActivityLog::record(
            'quota_exceeded', 'api',
            "API quota exceeded for {$event->integration->display_name} ({$event->integration->usagePercent()}%)",
            $event->integration,
        );
    }
}
