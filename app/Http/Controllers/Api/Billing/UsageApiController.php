<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\UsageLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsageApiController extends Controller
{
    public function __construct(private readonly UsageLimitService $usageLimit) {}

    /** GET /api/billing/usage */
    public function index(): JsonResponse
    {
        $tenant = app('current.tenant');
        $all    = $this->usageLimit->allForTenant($tenant->id);

        return response()->json([
            'data' => $all->map(fn ($u) => [
                'feature'     => $u->feature_name,
                'current'     => $u->current_usage,
                'limit'       => $u->usage_limit,
                'unlimited'   => $u->isUnlimited(),
                'remaining'   => $u->remaining(),
                'percentage'  => $u->percentageUsed(),
                'near_limit'  => $u->isNearLimit(),
                'reset_date'  => $u->reset_date?->toDateString(),
            ]),
        ]);
    }

    /** GET /api/billing/usage/{feature} */
    public function show(string $feature): JsonResponse
    {
        $tenant   = app('current.tenant');
        $tracking = \App\Models\UsageTracking::where('tenant_id', $tenant->id)->where('feature_name', $feature)->first();

        if (! $tracking) {
            return response()->json(['message' => 'Feature not tracked.'], 404);
        }

        return response()->json([
            'data' => [
                'feature'    => $tracking->feature_name,
                'current'    => $tracking->current_usage,
                'limit'      => $tracking->usage_limit,
                'unlimited'  => $tracking->isUnlimited(),
                'remaining'  => $tracking->remaining(),
                'percentage' => $tracking->percentageUsed(),
                'near_limit' => $tracking->isNearLimit(),
                'reset_date' => $tracking->reset_date?->toDateString(),
            ],
        ]);
    }
}
