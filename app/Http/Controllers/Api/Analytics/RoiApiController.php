<?php

namespace App\Http\Controllers\Api\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Http\Controllers\Controller;
use App\Services\Analytics\RoiCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoiApiController extends Controller
{
    public function __construct(private RoiCalculationService $service) {}

    public function summary(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->service->summary($filter));
    }

    public function byPlatform(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->service->byPlatform($filter));
    }
}
