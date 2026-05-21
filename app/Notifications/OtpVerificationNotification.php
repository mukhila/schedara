<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $name
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your Schedara account')
            ->greeting("Hi {$this->name},")
            ->line('Use the code below to verify your email address. It expires in 10 minutes.')
            ->line("**{$this->code}**")
            ->line('If you did not create a Schedara account, you can safely ignore this email.');
    }
}
