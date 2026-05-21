<?php

namespace App\Notifications\Post;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Post   $post,
        public readonly string $reason = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->post->title ?: str($this->post->content)->limit(50)->toString();

        return (new MailMessage)
            ->subject("Post failed to publish — Schedara")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your post **{$label}** failed to publish.")
            ->when($this->reason, fn ($m) => $m->line("Reason: {$this->reason}"))
            ->action('Review Post', url("/posts/{$this->post->uuid}"))
            ->line('Please review and try again.');
    }
}
