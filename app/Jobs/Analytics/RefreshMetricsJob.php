<?php

namespace App\Jobs\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Services\Analytics\AnalyticsDashboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshMetricsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public readonly int $tenantId) {}

    public function uniqueId(): string { return "refresh-metrics-{$this->tenantId}"; }

    public function handle(AnalyticsDashboardService $service): void
    {
        $filter = new AnalyticsFilterDTO(
            tenantId: $this->tenantId,
            range:    DateRangeDTO::lastMonth(),
        );

        $service->invalidateCache($this->tenantId);
        $service->overview($filter); // warms the cache
    }
}
