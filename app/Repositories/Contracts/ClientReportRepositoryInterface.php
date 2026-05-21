<?php

namespace App\Repositories\Contracts;

use App\Models\ClientReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClientReportRepositoryInterface
{
    public function forWorkspace(int $workspaceId, array $filters = []): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?ClientReport;

    public function create(int $workspaceId, int $generatedBy, array $data): ClientReport;

    public function markReady(ClientReport $report, string $filePath, string $fileUrl): ClientReport;

    public function markFailed(ClientReport $report, ?string $error = null): ClientReport;
}
