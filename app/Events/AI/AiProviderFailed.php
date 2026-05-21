<?php

namespace App\Events\AI;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiProviderFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $provider,
        public readonly string $requestType,
        public readonly string $errorMessage,
    ) {}
}
