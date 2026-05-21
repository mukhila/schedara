<?php

namespace App\Http\Controllers\Api\Collaboration;

use App\Http\Controllers\Controller;
use App\Services\Collaboration\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogApiController extends Controller
{
    public function __construct(private readonly ActivityLogService $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenant  = app('current.tenant');
        $filters = $request->only(['user_id', 'module', 'action', 'from', 'to', 'per_page']);

        return response()->json([
            'data'    => $this->service->forTenant($tenant->id, $filters),
            'modules' => $this->service->availableModules($tenant->id),
        ]);
    }

    public function recent(): JsonResponse
    {
        $tenant = app('current.tenant');
        return response()->json([
            'data' => $this->service->recentForTenant($tenant->id),
        ]);
    }
}
