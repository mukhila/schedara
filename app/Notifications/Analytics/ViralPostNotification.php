<?php

namespace App\Notifications\Analytics;

use App\Models\PostAnalytic;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ViralPostNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly PostAnalytic $analytic,
        public readonly float        $engagementRate,
    ) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Viral Post Detected ({$this->engagementRate}% engagement)")
            ->line("One of your posts is going viral with **{$this->engagementRate}%** engagement rate on {$this->analytic->platform}.")
            ->action('View Analytics', url('/analytics'));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'viral_post',
            'title'   => 'Viral Post Detected',
            'message' => "{$this->engagementRate}% engagement on {$this->analytic->platform}",
            'url'     => '/analytics',
        ];
    }
}
