<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\WidgetAiInsightsResource;
use App\Http\Resources\Dashboard\WidgetEngagementResource;
use App\Http\Resources\Dashboard\WidgetFollowersResource;
use App\Http\Resources\Dashboard\WidgetPlatformComparisonResource;
use App\Http\Resources\Dashboard\WidgetPostPerformanceResource;
use App\Http\Resources\Dashboard\WidgetRevenueResource;
use App\Services\Analytics\AiPredictiveAnalyticsService;
use App\Services\Dashboard\EngagementWidgetService;
use App\Services\Dashboard\FollowerWidgetService;
use App\Services\Dashboard\PlatformComparisonService;
use App\Services\Dashboard\PostPerformanceService;
use App\Services\Dashboard\RevenueInsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

class WidgetApiController extends Controller
{
    public function __construct(
        private EngagementWidgetService    $engagement,
        private FollowerWidgetService      $followers,
        private PostPerformanceService     $postPerf,
        private PlatformComparisonService  $platforms,
        private RevenueInsightService      $revenue,
        private AiPredictiveAnalyticsService $ai,
    ) {}

    public function engagement(Request $request): JsonResponse
    {
        $filter = $this->buildFilter($request);
        $data   = $this->engagement->get($filter);

        return (new WidgetEngagementResource($data))
            ->additional($this->meta($filter))
            ->response();
    }

    public function followers(Request $request): JsonResponse
    {
        $filter = $this->buildFilter($request);
        $data   = $this->followers->get($filter);

        return (new WidgetFollowersResource($data))
            ->additional($this->meta($filter))
            ->response();
    }

    public function postPerformance(Request $request): JsonResponse
    {
        $filter = $this->buildFilter($request);
        $sortBy = $request->input('sort_by', 'engagement_count');
        $data   = $this->postPerf->get($filter, $sortBy);

        return (new WidgetPostPerformanceResource($data))
            ->additional($this->meta($filter))
            ->response();
    }

    public function platformComparison(Request $request): JsonResponse
    {
        $filter = $this->buildFilter($request);
        $data   = $this->platforms->get($filter);

        return (new WidgetPlatformComparisonResource($data))
            ->additional($this->meta($filter))
            ->response();
    }

    public function revenue(Request $request): JsonResponse
    {
        $filter = $this->buildFilter($request);
        $data   = $this->revenue->get($filter);

        return (new WidgetRevenueResource($data))
            ->additional($this->meta($filter))
            ->response();
    }

    public function aiInsights(Request $request): JsonResponse
    {
        $filter = $this->buildFilter($request);
        $tenant = app('current.tenant');

        $data = [
            'forecast'     => $this->ai->predict($filter),
            'viral_posts'  => $this->ai->detectViralPosts($tenant->id),
            'insights'     => [],
            'best_time'    => null,
            'generated_at' => now()->toIso8601String(),
        ];

        return (new WidgetAiInsightsResource($data))
            ->additional($this->meta($filter))
            ->response();
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function buildFilter(Request $request): AnalyticsFilterDTO
    {
        $tenant = app('current.tenant');

        $range = ($request->has('from') && $request->has('to'))
            ? new DateRangeDTO($request->input('from'), $request->input('to'))
            : DateRangeDTO::lastDays(30);

        $platforms = $request->has('platforms')
            ? explode(',', $request->input('platforms'))
            : null;

        return new AnalyticsFilterDTO(
            tenantId:  $tenant->id,
            range:     $range,
            platforms: $platforms,
            groupBy:   $request->input('group_by', 'day'),
        );
    }

    private function meta(AnalyticsFilterDTO $filter): array
    {
        return [
            'meta' => [
                'from'         => $filter->range->fromString(),
                'to'           => $filter->range->toString(),
                'days'         => $filter->range->diffInDays(),
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
