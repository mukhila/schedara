<?php

namespace App\Http\Controllers;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Services\Analytics\AiPredictiveAnalyticsService;
use App\Services\Analytics\AnalyticsDashboardService;
use App\Services\Analytics\FollowerAnalyticsService;
use App\Services\Analytics\RoiCalculationService;
use App\Services\Dashboard\DashboardLayoutService;
use App\Services\Dashboard\PostPerformanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsDashboardService  $dashboard,
        private FollowerAnalyticsService   $followers,
        private RoiCalculationService      $roi,
        private PostPerformanceService     $postPerf,
        private AiPredictiveAnalyticsService $ai,
        private DashboardLayoutService     $layouts,
    ) {}

    public function index(Request $request): View
    {
        $tenant = app('current.tenant');
        $user   = $request->user();

        $range = ($request->has('date_from') && $request->has('date_to'))
            ? new DateRangeDTO($request->date_from, $request->date_to)
            : DateRangeDTO::lastDays(30);

        $filter = new AnalyticsFilterDTO(tenantId: $tenant->id, range: $range);

        $overview    = $this->dashboard->overview($filter);
        $kpi         = $overview['kpi'] ?? [];
        $followerKpi = $overview['followers'] ?? [];
        $timeSeries  = $overview['time_series'] ?? [];
        $byPlatform  = $overview['by_platform'] ?? [];
        $campaigns   = $overview['campaigns'] ?? [];
        $dateRange   = $overview['date_range'] ?? [];

        // Post performance (top 10)
        $postPerf = $this->postPerf->get($filter);

        // Revenue KPIs
        $roiSummary = $this->roi->summary($filter);

        // AI insights (only when AI is enabled; avoid adding latency otherwise)
        $aiInsights = config('analytics.ai_analysis_enabled')
            ? ['forecast' => $this->ai->predict($filter), 'viral' => $this->ai->detectViralPosts($tenant->id)]
            : null;

        // Dashboard layout (order + visibility per user)
        $layout = $this->layouts->toArray($this->layouts->get($user->id, $tenant->id));

        return view('backend.dashboard', compact(
            'kpi', 'followerKpi', 'timeSeries', 'byPlatform', 'campaigns',
            'dateRange', 'filter', 'postPerf', 'roiSummary', 'aiInsights', 'layout'
        ));
    }
}
