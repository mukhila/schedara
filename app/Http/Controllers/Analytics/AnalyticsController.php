<?php

namespace App\Http\Controllers\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Http\Controllers\Controller;
use App\Models\AnalyticsCampaign;
use App\Models\AnalyticsReport;
use App\Services\Analytics\CampaignAnalyticsService;
use App\Services\Analytics\DemographicsService;
use App\Services\Analytics\EngagementAnalyticsService;
use App\Services\Analytics\FollowerAnalyticsService;
use App\Services\Analytics\ReachAnalyticsService;
use App\Services\Analytics\ReportGenerationService;
use App\Services\Analytics\RoiCalculationService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private EngagementAnalyticsService $engagementService,
        private ReachAnalyticsService      $reachService,
        private FollowerAnalyticsService   $followerService,
        private CampaignAnalyticsService   $campaignService,
        private DemographicsService        $demographicsService,
        private RoiCalculationService      $roiService,
        private ReportGenerationService    $reportService,
    ) {}

    public function index(Request $request)
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);

        $engagement = $this->engagementService->summary($filter);
        $reach      = $this->reachService->summary($filter);
        $followers  = $this->followerService->summary($filter);

        return view('backend.analytics.index', compact('filter', 'engagement', 'reach', 'followers'));
    }

    public function engagement(Request $request)
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);
        $data   = $this->engagementService->summary($filter);

        return view('backend.analytics.engagement', compact('filter', 'data'));
    }

    public function reach(Request $request)
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);
        $data   = $this->reachService->summary($filter);

        return view('backend.analytics.reach', compact('filter', 'data'));
    }

    public function followers(Request $request)
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);
        $data   = $this->followerService->summary($filter);

        return view('backend.analytics.followers', compact('filter', 'data'));
    }

    public function campaigns(Request $request)
    {
        $tenant    = app('current.tenant');
        $filter    = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);
        $campaigns = $this->campaignService->list($filter, 20);
        $summary   = $this->campaignService->summary($filter);

        return view('backend.analytics.campaigns', compact('filter', 'campaigns', 'summary'));
    }

    public function demographics(Request $request)
    {
        $tenant = app('current.tenant');
        $filter = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);
        $data   = $this->demographicsService->summary($filter);

        return view('backend.analytics.demographics', compact('filter', 'data'));
    }

    public function roi(Request $request)
    {
        $tenant    = app('current.tenant');
        $filter    = AnalyticsFilterDTO::fromRequest($request->all(), $tenant->id);
        $summary   = $this->roiService->summary($filter);
        $byPlatform= $this->roiService->byPlatform($filter);

        return view('backend.analytics.roi', compact('filter', 'summary', 'byPlatform'));
    }

    public function reports(Request $request)
    {
        $tenant  = app('current.tenant');
        $reports = AnalyticsReport::forTenant($tenant->id)
            ->with('creator:id,name')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('backend.analytics.reports', compact('reports'));
    }

    public function createReport(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:191',
            'type'      => 'nullable|in:custom,engagement,follower,campaign,roi,demographic',
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'format'    => 'nullable|in:pdf,csv,xlsx',
        ]);

        $tenant = app('current.tenant');
        $this->reportService->create($tenant->id, auth()->id(), $data);

        return redirect()->route('analytics.reports')->with('success', 'Report queued. It will be ready shortly.');
    }
}
