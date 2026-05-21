<?php

namespace App\Services\Client;

use App\DTOs\Client\GenerateReportDTO;
use App\Jobs\Client\GenerateClientReportJob;
use App\Models\AgencyClient;
use App\Models\ClientReport;
use App\Models\ClientWorkspace;
use App\Models\User;
use App\Repositories\Contracts\ClientReportRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientReportService
{
    public function __construct(
        private readonly ClientReportRepositoryInterface $reportRepository,
    ) {}

    public function listReports(ClientWorkspace $workspace, array $filters = []): LengthAwarePaginator
    {
        return $this->reportRepository->forWorkspace($workspace->id, $filters);
    }

    public function requestReport(ClientWorkspace $workspace, User $user, GenerateReportDTO $dto): ClientReport
    {
        $report = $this->reportRepository->create($workspace->id, $user->id, [
            'report_name'    => $dto->reportName,
            'report_type'    => $dto->reportType,
            'format'         => $dto->format,
            'report_config'  => $dto->reportConfig,
            'is_scheduled'   => $dto->isScheduled,
            'schedule_cron'  => $dto->scheduleCron,
            'email_delivery' => $dto->emailDelivery,
        ]);

        // Dispatch background job
        GenerateClientReportJob::dispatch($report->id);

        return $report;
    }

    public function getReport(string $uuid): ?ClientReport
    {
        return $this->reportRepository->findByUuid($uuid);
    }

    public function deleteReport(ClientReport $report): void
    {
        if ($report->file_path && \Storage::exists($report->file_path)) {
            \Storage::delete($report->file_path);
        }

        $report->delete();
    }
}
