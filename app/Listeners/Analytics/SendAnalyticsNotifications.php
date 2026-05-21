<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\CampaignCompleted;
use App\Events\Analytics\ROIThresholdReached;
use App\Events\Analytics\ViralPostDetected;
use App\Notifications\Analytics\CampaignCompletedNotification;
use App\Notifications\Analytics\ROIAlertNotification;
use App\Notifications\Analytics\ViralPostNotification;
use App\Services\Notifications\NotificationService;

class SendAnalyticsNotifications
{
    public function __construct(private NotificationService $notifications) {}

    public function handleCampaignCompleted(CampaignCompleted $event): void
    {
        $owner = $event->campaign->tenant->owner();
        if (!$owner) {
            return;
        }

        $this->notifications->send(
            user:             $owner,
            type:             'campaign_completed',
            category:         'analytics',
            title:            'Campaign Completed',
            body:             "Your campaign \"{$event->campaign->name}\" has finished. ROI: {$event->campaign->roi}%.",
            payload:          ['campaign_uuid' => $event->campaign->uuid],
            actionUrl:        url("/analytics/campaigns/{$event->campaign->uuid}"),
            tenantId:         $event->campaign->tenant_id,
            mailNotification: new CampaignCompletedNotification($event->campaign),
        );
    }

    public function handleRoiThreshold(ROIThresholdReached $event): void
    {
        $owner = $event->campaign->tenant->owner();
        if (!$owner) {
            return;
        }

        $this->notifications->send(
            user:             $owner,
            type:             'roi_threshold',
            category:         'analytics',
            title:            'ROI Alert',
            body:             "\"{$event->campaign->name}\" hit {$event->roi}% ROI — above your alert threshold.",
            payload:          ['campaign_uuid' => $event->campaign->uuid, 'roi' => $event->roi],
            actionUrl:        url("/analytics/campaigns/{$event->campaign->uuid}"),
            priority:         'high',
            tenantId:         $event->campaign->tenant_id,
            mailNotification: new ROIAlertNotification($event->campaign, $event->roi),
        );
    }

    public function handleViralPost(ViralPostDetected $event): void
    {
        $owner = $event->analytic->tenant->owner();
        if (!$owner) {
            return;
        }

        $this->notifications->send(
            user:             $owner,
            type:             'viral_post',
            category:         'analytics',
            title:            'Viral Post Detected',
            body:             "A post on {$event->analytic->platform} is going viral with {$event->engagementRate}% engagement.",
            payload:          ['engagement_rate' => $event->engagementRate, 'platform' => $event->analytic->platform],
            actionUrl:        url('/analytics'),
            priority:         'high',
            tenantId:         $event->analytic->tenant_id ?? null,
            mailNotification: new ViralPostNotification($event->analytic, $event->engagementRate),
        );
    }
}
