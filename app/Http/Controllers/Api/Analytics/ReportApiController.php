<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsReport;
use App\Services\Analytics\ReportGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    public function __construct(private ReportGenerationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenant  = app('current.tenant');
        $reports = AnalyticsReport::forTenant($tenant->id)
            ->with('creator:id,name')
            ->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json($reports);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:191',
            'type'      => 'nullable|in:custom,engagement,follower,campaign,roi,demographic',
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'filters'   => 'nullable|array',
            'metrics'   => 'nullable|array',
            'format'    => 'nullable|in:pdf,csv,xlsx',
        ]);

        $tenant = app('current.tenant');
        $report = $this->service->create($tenant->id, auth()->id(), $data);

        return response()->json($report, 202);
    }

    public function show(string $uuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $report = AnalyticsReport::forTenant($tenant->id)->where('uuid', $uuid)->firstOrFail();

        return response()->json($report);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $report = AnalyticsReport::forTenant($tenant->id)->where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return response()->json(null, 204);
    }
}
