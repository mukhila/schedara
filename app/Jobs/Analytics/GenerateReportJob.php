<?php

namespace App\Jobs\Analytics;

use App\Models\AnalyticsLog;
use App\Models\AnalyticsReport;
use App\Services\Analytics\AnalyticsExportService;
use App\Services\Analytics\ReportGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 600;

    public function __construct(public readonly AnalyticsReport $report) {}

    public function handle(ReportGenerationService $service, AnalyticsExportService $exporter): void
    {
        $start = microtime(true);

        try {
            $this->report->update(['status' => 'processing']);

            $summary = $service->buildSummary($this->report);
            $this->report->update(['summary' => $summary]);

            // Generate actual file
            $fileUrl = match ($this->report->format) {
                'csv'   => $exporter->generateCsv($this->report),
                default => $exporter->generateCsv($this->report), // PDF stub uses CSV for now
            };

            $service->markReady($this->report, $fileUrl);

            AnalyticsLog::record(
                $this->report->tenant_id,
                'generate_report',
                'success',
                ['report_id' => $this->report->uuid, 'format' => $this->report->format],
                null,
                null,
                (int) ((microtime(true) - $start) * 1000)
            );

        } catch (\Throwable $e) {
            $service->markFailed($this->report);

            AnalyticsLog::record(
                $this->report->tenant_id,
                'generate_report',
                'error',
                ['report_id' => $this->report->uuid],
                null,
                $e->getMessage(),
                (int) ((microtime(true) - $start) * 1000)
            );

            throw $e;
        }
    }
}
