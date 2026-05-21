<?php

namespace App\Listeners\AI;

use App\Events\AI\AiContentGenerated;
use Illuminate\Support\Facades\Log;

class TrackAiUsage
{
    public function handle(AiContentGenerated $event): void
    {
        Log::info('AI content generated', [
            'tenant_id'    => $event->tenantId,
            'user_id'      => $event->userId,
            'content_type' => $event->contentType,
            'content_id'   => $event->contentId,
        ]);
    }
}
