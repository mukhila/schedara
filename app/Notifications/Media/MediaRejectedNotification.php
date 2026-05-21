<?php

namespace App\Notifications\Media;

use App\Models\MediaLibrary;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MediaRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly MediaLibrary $media) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Media rejected — Schedara")
            ->line("Your file **{$this->media->name}** was rejected during approval review.")
            ->action('View Media', url("/cms/{$this->media->uuid}"))
            ->line('Please update the file and resubmit for approval.');
    }
}
