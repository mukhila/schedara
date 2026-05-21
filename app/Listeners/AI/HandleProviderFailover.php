<?php

namespace App\Listeners\AI;

use App\Events\AI\AiProviderFailed;
use Illuminate\Support\Facades\Log;

class HandleProviderFailover
{
    public function handle(AiProviderFailed $event): void
    {
        Log::warning('AI provider failed — failover triggered', [
            'tenant_id'    => $event->tenantId,
            'provider'     => $event->provider,
            'request_type' => $event->requestType,
            'error'        => $event->errorMessage,
        ]);
    }
}
