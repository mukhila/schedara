<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use App\Services\Billing\RevenueDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAnalyticsApiController extends Controller
{
    public function __construct(
        private AdminDashboardService  $dashboard,
        private RevenueDashboardService $revenue,
    ) {}

    public function platformStats(): JsonResponse
    {
        return response()->json($this->dashboard->getPlatformStats());
    }

    public function revenueByMonth(Request $request): JsonResponse
    {
        $months = (int) $request->get('months', 12);

        return response()->json($this->revenue->getRevenueByMonth(min($months, 24)));
    }

    public function userGrowth(Request $request): JsonResponse
    {
        $months = (int) $request->get('months', 6);

        return response()->json($this->dashboard->getUserGrowth(min($months, 12)));
    }

    public function tenantGrowth(Request $request): JsonResponse
    {
        $months = (int) $request->get('months', 6);

        return response()->json($this->dashboard->getTenantGrowth(min($months, 12)));
    }
}
