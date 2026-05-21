<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset your Schedara password')
            ->greeting('Hello,')
            ->line('Use the code below to reset your password. It expires in 15 minutes.')
            ->line("**{$this->code}**")
            ->line('If you did not request a password reset, no action is needed.');
    }
}
