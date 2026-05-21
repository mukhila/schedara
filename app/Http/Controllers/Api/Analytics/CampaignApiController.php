<?php

namespace App\Http\Controllers\Api\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Http\Controllers\Controller;
use App\Services\Analytics\CampaignAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignApiController extends Controller
{
    public function __construct(private CampaignAnalyticsService $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->service->list($filter, (int) $request->input('per_page', 20)));
    }

    public function summary(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->service->summary($filter));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:191',
            'platform'   => 'nullable|string|max:32',
            'status'     => 'nullable|in:draft,active,paused',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after:start_date',
            'budget'     => 'nullable|numeric|min:0',
            'tags'       => 'nullable|array',
        ]);

        $tenant   = app('current.tenant');
        $campaign = $this->service->create($tenant->id, auth()->id(), $data);

        return response()->json($campaign, 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $campaign = \App\Models\AnalyticsCampaign::where('uuid', $uuid)->firstOrFail();
        $metrics  = $request->validate([
            'spend'       => 'nullable|numeric|min:0',
            'revenue'     => 'nullable|numeric|min:0',
            'impressions' => 'nullable|integer|min:0',
            'clicks'      => 'nullable|integer|min:0',
            'conversions' => 'nullable|integer|min:0',
            'reach'       => 'nullable|integer|min:0',
            'engagement'  => 'nullable|integer|min:0',
            'status'      => 'nullable|in:draft,active,paused,completed',
        ]);

        return response()->json($this->service->updateMetrics($campaign, $metrics));
    }

    public function topPerformers(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $limit  = (int) $request->input('limit', 5);

        return response()->json($this->service->topPerformers($tenant->id, $limit));
    }
}
