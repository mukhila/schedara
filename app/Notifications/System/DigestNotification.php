<?php

namespace App\Notifications\System;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DigestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Collection $notifications,
        public readonly string     $period = 'weekly',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count  = $this->notifications->count();
        $label  = $this->period === 'daily' ? "today's" : "this week's";
        $unread = $this->notifications->whereNull('read_at')->count();

        $mail = (new MailMessage)
            ->subject("Your {$label} Schedara digest — {$count} notifications")
            ->greeting("Hi {$notifiable->name},")
            ->line("You have **{$count} notifications** ({$unread} unread) from " . ($this->period === 'daily' ? 'today' : 'this week') . '.');

        foreach ($this->notifications->take(10) as $notif) {
            $mail->line("• **{$notif->title()}** — {$notif->body()}");
        }

        if ($count > 10) {
            $mail->line("... and " . ($count - 10) . " more.");
        }

        return $mail
            ->action('View All Notifications', url('/notifications'))
            ->line('You can manage your notification preferences at any time.');
    }
}
