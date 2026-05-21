<?php

namespace App\Notifications\Post;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostScheduledNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Post $post) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->post->title ?: str($this->post->content)->limit(50)->toString();
        $when  = $this->post->scheduled_at?->format('M j, Y g:ia') ?? 'soon';

        return (new MailMessage)
            ->subject("Post scheduled — Schedara")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your post **{$label}** is scheduled to publish at **{$when}**.")
            ->action('View Post', url("/posts/{$this->post->uuid}"));
    }
}
