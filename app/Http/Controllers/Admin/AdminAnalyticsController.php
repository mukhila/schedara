<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function __construct(private AdminDashboardService $dashboard) {}

    public function index(): View
    {
        $stats        = $this->dashboard->getPlatformStats();
        $userGrowth   = $this->dashboard->getUserGrowth(12);
        $tenantGrowth = $this->dashboard->getTenantGrowth(12);

        $ticketStats = Cache::remember('admin.ticket_stats', 120, function () {
            return SupportTicket::selectRaw("
                COUNT(*) as total,
                SUM(status IN ('open','in_progress','waiting')) as open_count,
                SUM(status = 'resolved') as resolved_count,
                SUM(priority = 'critical') as critical_count,
                AVG(CASE WHEN first_response_at IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE, created_at, first_response_at) END) as avg_response_minutes
            ")->first()->toArray();
        });

        $usersByDay = Cache::remember('admin.users_by_day.30', 120, function () {
            return User::selectRaw('DATE(created_at) as day, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('day')
                ->orderBy('day')
                ->pluck('count', 'day')
                ->toArray();
        });

        $failedJobsByQueue = Cache::remember('admin.failed_jobs_queue', 60, function () {
            return DB::table('failed_jobs')
                ->selectRaw('queue, COUNT(*) as count')
                ->groupBy('queue')
                ->pluck('count', 'queue')
                ->toArray();
        });

        return view('admin.analytics.index', compact(
            'stats', 'userGrowth', 'tenantGrowth',
            'ticketStats', 'usersByDay', 'failedJobsByQueue'
        ));
    }
}
