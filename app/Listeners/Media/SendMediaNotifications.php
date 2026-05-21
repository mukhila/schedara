<?php

namespace App\Listeners\Media;

use App\Events\Media\ContentApproved;
use App\Events\Media\ContentRejected;
use App\Events\Media\MediaUploaded;
use App\Notifications\Media\MediaApprovedNotification;
use App\Notifications\Media\MediaRejectedNotification;
use App\Services\Notifications\NotificationService;

class SendMediaNotifications
{
    public function __construct(private NotificationService $notifications) {}

    public function handleUploaded(MediaUploaded $event): void
    {
        $uploader = $event->media->uploader;
        if (!$uploader) {
            return;
        }

        $this->notifications->send(
            user:      $uploader,
            type:      'media_uploaded',
            category:  'media',
            title:     'Media Uploaded',
            body:      "\"{$event->media->name}\" has been uploaded and is being processed.",
            payload:   ['media_uuid' => $event->media->uuid, 'media_type' => $event->media->type],
            actionUrl: url("/cms/{$event->media->uuid}"),
            tenantId:  $event->media->tenant_id ?? null,
        );
    }

    public function handleApproved(ContentApproved $event): void
    {
        $uploader = $event->media->uploader;
        if (!$uploader) {
            return;
        }

        $this->notifications->send(
            user:             $uploader,
            type:             'media_approved',
            category:         'media',
            title:            'Media Approved',
            body:             "\"{$event->media->name}\" has been approved and is ready to use.",
            payload:          ['media_uuid' => $event->media->uuid],
            actionUrl:        url("/cms/{$event->media->uuid}"),
            tenantId:         $event->media->tenant_id ?? null,
            mailNotification: new MediaApprovedNotification($event->media),
        );
    }

    public function handleRejected(ContentRejected $event): void
    {
        $uploader = $event->media->uploader;
        if (!$uploader) {
            return;
        }

        $this->notifications->send(
            user:             $uploader,
            type:             'media_rejected',
            category:         'media',
            title:            'Media Rejected',
            body:             "\"{$event->media->name}\" was rejected during review.",
            payload:          ['media_uuid' => $event->media->uuid],
            actionUrl:        url("/cms/{$event->media->uuid}"),
            priority:         'high',
            tenantId:         $event->media->tenant_id ?? null,
            mailNotification: new MediaRejectedNotification($event->media),
        );
    }
}
