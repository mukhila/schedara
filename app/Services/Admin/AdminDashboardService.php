<?php

namespace App\Services\Admin;

use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Billing\RevenueDashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function __construct(private RevenueDashboardService $revenue) {}

    public function getPlatformStats(): array
    {
        return Cache::remember('admin.platform_stats', 120, function () {
            $revenueSummary = $this->revenue->getSummary();

            return [
                'total_users'      => User::count(),
                'new_users_today'  => User::whereDate('created_at', today())->count(),
                'active_tenants'   => Tenant::where('status', 'active')->count(),
                'total_tenants'    => Tenant::count(),
                'mrr'              => $revenueSummary['mrr'] ?? 0,
                'arr'              => $revenueSummary['arr'] ?? 0,
                'open_tickets'     => SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting'])->count(),
                'failed_jobs'      => DB::table('failed_jobs')->count(),
                'total_revenue'    => $revenueSummary['total_revenue_30d'] ?? 0,
                'active_subs'      => $revenueSummary['active_subscriptions'] ?? 0,
                'churn_rate'       => $revenueSummary['churn_rate'] ?? 0,
            ];
        });
    }

    public function getRecentActivity(int $limit = 20): \Illuminate\Support\Collection
    {
        return \App\Models\AdminActivityLog::with('admin')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getUserGrowth(int $months = 6): array
    {
        return Cache::remember("admin.user_growth.{$months}", 300, function () use ($months) {
            $rows = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $result = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $result[$key] = $rows[$key] ?? 0;
            }
            return $result;
        });
    }

    public function getTenantGrowth(int $months = 6): array
    {
        return Cache::remember("admin.tenant_growth.{$months}", 300, function () use ($months) {
            $rows = Tenant::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $result = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $result[$key] = $rows[$key] ?? 0;
            }
            return $result;
        });
    }

    public function bustCache(): void
    {
        Cache::forget('admin.platform_stats');
    }
}
