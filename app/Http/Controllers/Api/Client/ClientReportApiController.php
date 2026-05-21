<?php

namespace App\Http\Controllers\Api\Client;

use App\DTOs\Client\GenerateReportDTO;
use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Models\ClientWorkspace;
use App\Services\Client\ClientReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientReportApiController extends Controller
{
    public function __construct(
        private readonly ClientReportService $reportService,
    ) {}

    public function index(Request $request, string $workspaceUuid): JsonResponse
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);
        $filters   = $request->only(['type', 'status', 'per_page']);
        $reports   = $this->reportService->listReports($workspace, $filters);

        return response()->json($reports);
    }

    public function store(Request $request, string $workspaceUuid): JsonResponse
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);

        $validated = $request->validate([
            'report_name'   => 'required|string|max:255',
            'report_type'   => ['required', Rule::in(['social', 'engagement', 'roi', 'campaign', 'seo', 'ai_insights'])],
            'format'        => ['sometimes', Rule::in(['pdf', 'excel', 'csv'])],
            'report_config' => 'nullable|array',
            'email_delivery'=> 'boolean',
            'is_scheduled'  => 'boolean',
            'schedule_cron' => 'nullable|string',
        ]);

        $dto    = GenerateReportDTO::fromArray($validated);
        $report = $this->reportService->requestReport($workspace, $request->user(), $dto);

        return response()->json([
            'message' => 'Report generation queued.',
            'report'  => $report,
        ], 202);
    }

    public function show(string $uuid): JsonResponse
    {
        $report = $this->reportService->getReport($uuid);

        if (!$report) {
            return response()->json(['message' => 'Report not found.'], 404);
        }

        return response()->json($report);
    }

    public function download(string $uuid): mixed
    {
        $report = $this->reportService->getReport($uuid);

        if (!$report || !$report->isReady() || !Storage::exists($report->file_path)) {
            return response()->json(['message' => 'Report not available.'], 404);
        }

        return Storage::download($report->file_path, $report->report_name . '.pdf');
    }

    public function destroy(string $uuid): JsonResponse
    {
        $report = $this->reportService->getReport($uuid);

        if (!$report) {
            return response()->json(['message' => 'Report not found.'], 404);
        }

        $this->reportService->deleteReport($report);

        return response()->json(['message' => 'Report deleted.']);
    }

    private function resolveWorkspace(string $uuid): ClientWorkspace
    {
        $tenant = app('tenant');

        return ClientWorkspace::whereHas('client', fn ($q) => $q->where('agency_id', $tenant->id))
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
