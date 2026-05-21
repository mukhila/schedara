<?php

namespace App\Http\Controllers\Api\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Http\Controllers\Controller;
use App\Repositories\Analytics\AnalyticsMetricsRepository;
use App\Services\Analytics\EngagementAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EngagementApiController extends Controller
{
    public function __construct(
        private EngagementAnalyticsService  $service,
        private AnalyticsMetricsRepository  $repo,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->service->summary($filter));
    }

    public function topPosts(Request $request): JsonResponse
    {
        $tenant  = app('current.tenant');
        $filter  = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);
        $sortBy  = $request->input('sort_by', 'engagement_rate');

        return response()->json($this->repo->topPosts($filter, $sortBy));
    }

    public function byPlatform(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->repo->byPlatform($filter));
    }
}
