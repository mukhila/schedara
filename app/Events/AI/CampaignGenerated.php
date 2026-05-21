<?php

namespace App\Events\AI;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $tenantId,
        public readonly int    $userId,
        public readonly int    $contentId,
        public readonly string $campaignName,
    ) {}
}
