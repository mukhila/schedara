<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private AdminDashboardService $dashboard) {}

    public function index(): View
    {
        $stats        = $this->dashboard->getPlatformStats();
        $recentActivity = $this->dashboard->getRecentActivity(15);
        $userGrowth   = $this->dashboard->getUserGrowth(6);
        $tenantGrowth = $this->dashboard->getTenantGrowth(6);

        return view('admin.dashboard', compact('stats', 'recentActivity', 'userGrowth', 'tenantGrowth'));
    }
}
