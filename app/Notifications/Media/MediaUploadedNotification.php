<?php

namespace App\Notifications\Media;

use App\Models\MediaLibrary;
use Illuminate\Notifications\Notification;

class MediaUploadedNotification extends Notification
{
    public function __construct(public readonly MediaLibrary $media) {}

    public function via(object $notifiable): array
    {
        return [];
    }
}
