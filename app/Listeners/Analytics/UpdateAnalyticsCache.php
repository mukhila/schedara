<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\AnalyticsUpdated;
use Illuminate\Support\Facades\Cache;

class UpdateAnalyticsCache
{
    public function handle(AnalyticsUpdated $event): void
    {
        $tid = $event->tenantId;

        // Bust all analytics cache for this tenant
        foreach (['overview', 'engagement', 'reach', 'followers', 'campaigns:summary', 'roi', 'demographics'] as $ns) {
            Cache::forget("analytics:{$ns}:{$tid}:*");
        }
    }
}
