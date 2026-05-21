<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\AnalyticsUpdated;
use App\Services\Analytics\AnalyticsDashboardService;
use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;

class UpdateDashboard
{
    public function __construct(private AnalyticsDashboardService $dashboard) {}

    public function handle(AnalyticsUpdated $event): void
    {
        // Bust the cache first so the next overview() call is fresh
        $this->dashboard->invalidateCache($event->tenantId);

        // Pre-warm the cache with a 30-day window so the next page load is instant
        $filter = new AnalyticsFilterDTO(
            tenantId: $event->tenantId,
            range:    DateRangeDTO::lastDays(30),
        );

        $this->dashboard->overview($filter);
    }
}
