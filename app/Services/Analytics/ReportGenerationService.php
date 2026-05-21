<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Jobs\Analytics\GenerateReportJob;
use App\Models\AnalyticsReport;
use Illuminate\Support\Str;

class ReportGenerationService
{
    public function __construct(
        private AnalyticsDashboardService $dashboardService,
        private EngagementAnalyticsService $engagementService,
        private RoiCalculationService $roiService,
    ) {}

    public function create(int $tenantId, int $userId, array $data): AnalyticsReport
    {
        $report = AnalyticsReport::create([
            'tenant_id'  => $tenantId,
            'created_by' => $userId,
            'name'       => $data['name'],
            'type'       => $data['type'] ?? 'custom',
            'status'     => 'pending',
            'date_from'  => $data['date_from'],
            'date_to'    => $data['date_to'],
            'filters'    => $data['filters'] ?? null,
            'metrics'    => $data['metrics'] ?? null,
            'format'     => $data['format'] ?? 'pdf',
            'expires_at' => now()->addDays(7),
        ]);

        GenerateReportJob::dispatch($report);

        return $report;
    }

    public function buildSummary(AnalyticsReport $report): array
    {
        $filter = new AnalyticsFilterDTO(
            tenantId: $report->tenant_id,
            range:    new \App\DTOs\Analytics\DateRangeDTO($report->date_from->toDateString(), $report->date_to->toDateString()),
            platforms:$report->filters['platforms'] ?? null,
        );

        return match ($report->type) {
            'engagement' => $this->engagementService->summary($filter),
            'roi'        => $this->roiService->summary($filter),
            default      => $this->dashboardService->overview($filter),
        };
    }

    public function markReady(AnalyticsReport $report, ?string $filePath = null): void
    {
        $report->update([
            'status'       => 'ready',
            'file_path'    => $filePath,
            'generated_at' => now(),
        ]);
    }

    public function markFailed(AnalyticsReport $report): void
    {
        $report->update(['status' => 'failed']);
    }
}
