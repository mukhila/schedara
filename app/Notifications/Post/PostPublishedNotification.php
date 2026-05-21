<?php

namespace App\Notifications\Post;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostPublishedNotification extends Notification
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

        return (new MailMessage)
            ->subject("Post published — Schedara")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your post **{$label}** has been published successfully.")
            ->action('View Post', url("/posts/{$this->post->uuid}"))
            ->line('Keep creating great content!');
    }
}
