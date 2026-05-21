<?php

namespace App\Events\Post;

use App\Models\PostMedia;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaProcessed
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly PostMedia $media) {}
}
