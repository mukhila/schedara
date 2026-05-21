<?php

namespace App\Notifications\Analytics;

use App\Models\AnalyticsCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly AnalyticsCampaign $campaign) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Campaign Completed: {$this->campaign->name}")
            ->line("Your campaign **{$this->campaign->name}** has completed.")
            ->line("ROI: {$this->campaign->roi}% | Revenue: \${$this->campaign->revenue}")
            ->action('View Campaign', url("/analytics/campaigns/{$this->campaign->uuid}"));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'campaign_completed',
            'title'   => "Campaign Completed: {$this->campaign->name}",
            'message' => "ROI: {$this->campaign->roi}%",
            'url'     => "/analytics/campaigns/{$this->campaign->uuid}",
        ];
    }
}
