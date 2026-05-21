<?php

namespace App\Mail;

use App\Models\AgencyClient;
use App\Models\ClientWorkspace;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientWelcomeMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly AgencyClient $client,
        public readonly ClientWorkspace $workspace,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to your new workspace!');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.client.welcome');
    }
}
