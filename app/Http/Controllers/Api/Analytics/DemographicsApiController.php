<?php

namespace App\Http\Controllers\Api\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Http\Controllers\Controller;
use App\Services\Analytics\DemographicsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemographicsApiController extends Controller
{
    public function __construct(private DemographicsService $service) {}

    public function summary(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        return response()->json($this->service->summary($filter));
    }
}
