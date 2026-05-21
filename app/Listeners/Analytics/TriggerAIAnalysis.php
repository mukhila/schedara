<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\AnalyticsUpdated;
use App\Jobs\Analytics\RefreshMetricsJob;

class TriggerAIAnalysis
{
    public function handle(AnalyticsUpdated $event): void
    {
        if (!config('analytics.ai_analysis_enabled', false)) {
            return;
        }

        RefreshMetricsJob::dispatch($event->tenantId)
            ->onQueue(config('analytics.queue', 'analytics'))
            ->delay(now()->addMinutes(2));
    }
}
