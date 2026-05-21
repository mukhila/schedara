<?php

namespace App\Listeners\Post;

use App\Events\Post\PostFailed;
use App\Events\Post\PostPublished;
use App\Events\Post\PostScheduled;
use App\Notifications\Post\PostFailedNotification;
use App\Notifications\Post\PostPublishedNotification;
use App\Notifications\Post\PostScheduledNotification;
use App\Services\Notifications\NotificationService;

class SendPostNotifications
{
    public function __construct(private NotificationService $notifications) {}

    public function handleScheduled(PostScheduled $event): void
    {
        $user = $event->post->user;
        if (!$user) {
            return;
        }

        $label = $event->post->title ?: str($event->post->content)->limit(50)->toString();

        $this->notifications->send(
            user:             $user,
            type:             'post_scheduled',
            category:         'post',
            title:            'Post Scheduled',
            body:             "\"{$label}\" will publish at " . ($event->post->scheduled_at?->format('M j, g:ia') ?? 'soon') . '.',
            payload:          ['post_uuid' => $event->post->uuid, 'platforms' => $event->post->platforms],
            actionUrl:        url("/posts/{$event->post->uuid}"),
            tenantId:         $event->post->tenant_id ?? null,
            mailNotification: new PostScheduledNotification($event->post),
        );
    }

    public function handlePublished(PostPublished $event): void
    {
        $user = $event->post->user;
        if (!$user) {
            return;
        }

        $label = $event->post->title ?: str($event->post->content)->limit(50)->toString();

        $this->notifications->send(
            user:             $user,
            type:             'post_published',
            category:         'post',
            title:            'Post Published',
            body:             "\"{$label}\" is now live.",
            payload:          ['post_uuid' => $event->post->uuid, 'platforms' => $event->post->platforms],
            actionUrl:        url("/posts/{$event->post->uuid}"),
            tenantId:         $event->post->tenant_id ?? null,
            mailNotification: new PostPublishedNotification($event->post),
        );
    }

    public function handleFailed(PostFailed $event): void
    {
        $user = $event->post->user;
        if (!$user) {
            return;
        }

        $label = $event->post->title ?: str($event->post->content)->limit(50)->toString();

        $this->notifications->send(
            user:             $user,
            type:             'post_failed',
            category:         'post',
            title:            'Post Failed to Publish',
            body:             "\"{$label}\" failed to publish." . ($event->reason ? " Reason: {$event->reason}" : ''),
            payload:          ['post_uuid' => $event->post->uuid, 'reason' => $event->reason],
            actionUrl:        url("/posts/{$event->post->uuid}"),
            priority:         'high',
            tenantId:         $event->post->tenant_id ?? null,
            mailNotification: new PostFailedNotification($event->post, $event->reason),
        );
    }
}
