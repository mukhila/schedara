<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketNotificationMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly string $event,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'replied'  => "Re: [{$this->ticket->ticket_number}] {$this->ticket->subject}",
            'resolved' => "[{$this->ticket->ticket_number}] Ticket Resolved",
            'closed'   => "[{$this->ticket->ticket_number}] Ticket Closed",
            'assigned' => "[{$this->ticket->ticket_number}] Ticket Assigned",
            default    => "[{$this->ticket->ticket_number}] Ticket Update",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket_notification');
    }
}
