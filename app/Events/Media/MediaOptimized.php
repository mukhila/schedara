<?php

namespace App\Events\Media;

use App\Models\MediaLibrary;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaOptimized
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly MediaLibrary $media) {}
}
