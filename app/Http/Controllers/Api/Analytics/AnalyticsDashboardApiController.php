<?php

namespace App\Http\Controllers\Api\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsDashboardService;
use App\Services\Analytics\AiPredictiveAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsDashboardApiController extends Controller
{
    public function __construct(
        private AnalyticsDashboardService    $dashboard,
        private AiPredictiveAnalyticsService $ai,
    ) {}

    public function overview(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->dashboard->overview($filter));
    }

    public function predict(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->ai->predict($filter));
    }

    public function viral(Request $request): JsonResponse
    {
        $tenant    = app('current.tenant');
        $threshold = (float) $request->input('threshold', 5.0);

        return response()->json($this->ai->detectViralPosts($tenant->id, $threshold));
    }
}
