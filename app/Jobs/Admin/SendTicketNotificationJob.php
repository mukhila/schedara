<?php

namespace App\Jobs\Admin;

use App\Mail\TicketNotificationMail;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTicketNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        private readonly int    $ticketId,
        private readonly string $event,
    ) {}

    public function handle(): void
    {
        $ticket = SupportTicket::with(['user', 'assignee'])->find($this->ticketId);

        if (! $ticket || ! $ticket->user) {
            return;
        }

        Mail::to($ticket->user->email, $ticket->user->name)
            ->send(new TicketNotificationMail($ticket, $this->event));
    }
}
