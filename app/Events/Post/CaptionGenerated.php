<?php

namespace App\Events\Post;

use App\Models\Post;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CaptionGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Post   $post,
        public readonly string $platform,
        public readonly string $caption,
    ) {}
}
