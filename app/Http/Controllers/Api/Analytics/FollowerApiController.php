<?php

namespace App\Http\Controllers\Api\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Http\Controllers\Controller;
use App\Services\Analytics\FollowerAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowerApiController extends Controller
{
    public function __construct(private FollowerAnalyticsService $service) {}

    public function summary(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->service->summary($filter));
    }

    public function growthRate(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $days   = (int) $request->input('days', 30);

        return response()->json([
            'days'        => $days,
            'growth_rate' => $this->service->growthRate($tenant->id, $days),
        ]);
    }
}
