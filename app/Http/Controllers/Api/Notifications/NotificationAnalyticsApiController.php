<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Services\Notifications\NotificationAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationAnalyticsApiController extends Controller
{
    public function __construct(
        private readonly NotificationAnalyticsService $analytics,
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $tenantId = app()->bound('current.tenant') ? app('current.tenant')->id : null;
        $days     = (int) $request->query('days', 30);

        return response()->json([
            'stats'            => $this->analytics->deliveryStats($tenantId, $days),
            'channel_breakdown' => $this->analytics->channelBreakdown($tenantId, $days),
            'daily_volume'     => $this->analytics->dailyVolume($tenantId, min($days, 14)),
            'recent_failures'  => $this->analytics->recentFailures($tenantId),
        ]);
    }
}
