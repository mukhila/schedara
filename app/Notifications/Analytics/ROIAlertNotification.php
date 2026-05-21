<?php

namespace App\Notifications\Analytics;

use App\Models\AnalyticsCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ROIAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly AnalyticsCampaign $campaign,
        public readonly float             $roi,
    ) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("ROI Alert: {$this->campaign->name} hit {$this->roi}%")
            ->line("Your campaign **{$this->campaign->name}** has reached an ROI of **{$this->roi}%**.")
            ->action('View Campaign', url("/analytics/campaigns/{$this->campaign->uuid}"));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'roi_alert',
            'title'   => "ROI Alert: {$this->campaign->name}",
            'message' => "ROI reached {$this->roi}%",
            'url'     => "/analytics/campaigns/{$this->campaign->uuid}",
        ];
    }
}
