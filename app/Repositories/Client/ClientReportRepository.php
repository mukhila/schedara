<?php

namespace App\Repositories\Client;

use App\Models\ClientReport;
use App\Repositories\Contracts\ClientReportRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientReportRepository implements ClientReportRepositoryInterface
{
    public function forWorkspace(int $workspaceId, array $filters = []): LengthAwarePaginator
    {
        $query = ClientReport::with('generatedBy')
            ->where('client_workspace_id', $workspaceId);

        if (!empty($filters['type'])) {
            $query->where('report_type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function findByUuid(string $uuid): ?ClientReport
    {
        return ClientReport::where('uuid', $uuid)->first();
    }

    public function create(int $workspaceId, int $generatedBy, array $data): ClientReport
    {
        return ClientReport::create(array_merge($data, [
            'client_workspace_id' => $workspaceId,
            'generated_by'        => $generatedBy,
            'status'              => 'pending',
        ]));
    }

    public function markReady(ClientReport $report, string $filePath, string $fileUrl): ClientReport
    {
        $report->update([
            'status'       => 'ready',
            'file_path'    => $filePath,
            'file_url'     => $fileUrl,
            'generated_at' => now(),
        ]);

        return $report->fresh();
    }

    public function markFailed(ClientReport $report, ?string $error = null): ClientReport
    {
        $report->update([
            'status'    => 'failed',
            'report_data' => array_merge($report->report_data ?? [], ['error' => $error]),
        ]);

        return $report->fresh();
    }
}
