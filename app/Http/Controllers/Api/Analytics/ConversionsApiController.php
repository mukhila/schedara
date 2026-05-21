<?php

namespace App\Http\Controllers\Api\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Http\Controllers\Controller;
use App\Services\Analytics\ClickTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversionsApiController extends Controller
{
    public function __construct(private ClickTrackingService $tracking) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $filter  = new AnalyticsFilterDTO(
            tenantId: $tenant->id,
            range:    new DateRangeDTO($from, $to),
        );

        return response()->json($this->tracking->summary($filter));
    }
}
