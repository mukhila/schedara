<?php

namespace App\Mail;

use App\Models\AgencyClient;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OnboardingReminderMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly AgencyClient $client,
        public readonly int $pending,
    ) {}

    public function envelope(): Envelope
    {
        $steps = $this->pending === 1 ? '1 step' : "{$this->pending} steps";

        return new Envelope(subject: "Complete your onboarding — {$steps} remaining");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client.onboarding-reminder',
            with: ['progress' => $this->client->onboardingProgress()],
        );
    }
}
