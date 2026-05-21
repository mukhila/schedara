<?php

namespace App\Listeners\Admin;

use App\Events\Admin\TicketCreated;
use App\Jobs\Admin\SendTicketNotificationJob;

class SendTicketNotifications
{
    public function handle(TicketCreated $event): void
    {
        SendTicketNotificationJob::dispatch($event->ticket->id, 'created')->onQueue('default');
    }
}
