<?php

namespace App\Events\AI;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiContentGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $tenantId,
        public readonly int    $userId,
        public readonly string $contentType,
        public readonly int    $contentId,
    ) {}
}
