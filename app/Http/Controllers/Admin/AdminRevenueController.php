<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Billing\RevenueDashboardService;
use Illuminate\View\View;

class AdminRevenueController extends Controller
{
    public function __construct(private RevenueDashboardService $revenue) {}

    public function index(): View
    {
        $summary        = $this->revenue->getSummary();
        $revenueByMonth = $this->revenue->getRevenueByMonth(12);
        $byPlan         = $this->revenue->getSubscriptionsByPlan();

        return view('admin.revenue.index', compact('summary', 'revenueByMonth', 'byPlan'));
    }
}
