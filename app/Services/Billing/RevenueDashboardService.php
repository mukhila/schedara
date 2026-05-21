<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RevenueDashboardService
{
    private int $cacheTtl = 300; // 5 minutes

    /**
     * Monthly Recurring Revenue in cents.
     * Active subscriptions: monthly + yearly/12.
     */
    public function getMrr(): int
    {
        return (int) Cache::remember('billing.mrr', $this->cacheTtl, function () {
            return Subscription::whereIn('status', ['active', 'trialing'])
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->selectRaw('
                    SUM(
                        CASE WHEN subscriptions.interval = "yearly"
                             THEN ROUND(plans.price_yearly / 12)
                             ELSE plans.price_monthly
                        END
                    ) as mrr
                ')
                ->value('mrr') ?? 0;
        });
    }

    /**
     * Annual Recurring Revenue = MRR × 12.
     */
    public function getArr(): int
    {
        return $this->getMrr() * 12;
    }

    /**
     * Churn rate: subscriptions cancelled in the last $days divided by total active at start.
     */
    public function getChurnRate(int $days = 30): float
    {
        return Cache::remember("billing.churn.{$days}", $this->cacheTtl, function () use ($days) {
            $since     = now()->subDays($days);
            $cancelled = Subscription::where('status', 'cancelled')
                ->where('cancel_at', '>=', $since)
                ->count();
            $total = Subscription::whereIn('status', ['active', 'trialing', 'cancelled'])
                ->where('created_at', '<', $since)
                ->count();

            return $total > 0 ? round($cancelled / $total * 100, 2) : 0.0;
        });
    }

    /**
     * New subscriptions created in the last $days.
     */
    public function getNewSubscriptions(int $days = 30): int
    {
        return Subscription::where('created_at', '>=', now()->subDays($days))->count();
    }

    /**
     * Active subscription count.
     */
    public function getActiveCount(): int
    {
        return Subscription::whereIn('status', ['active', 'trialing'])->count();
    }

    /**
     * Total revenue collected (paid invoices) in the last $days, in cents.
     */
    public function getTotalRevenue(int $days = 30): int
    {
        return (int) (Invoice::where('status', 'paid')
            ->where('paid_at', '>=', now()->subDays($days))
            ->sum('total') ?? 0);
    }

    /**
     * Revenue by month for the last $months.
     * Returns array of ['month' => 'Jan 2026', 'revenue' => 12345].
     */
    public function getRevenueByMonth(int $months = 12): array
    {
        return Cache::remember("billing.revenue_monthly.{$months}", $this->cacheTtl, function () use ($months) {
            $rows = Invoice::where('status', 'paid')
                ->where('paid_at', '>=', now()->subMonths($months))
                ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(total) as revenue")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('revenue', 'month')
                ->toArray();

            $result = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $result[] = [
                    'month'   => now()->subMonths($i)->format('M Y'),
                    'revenue' => (int) ($rows[$key] ?? 0),
                ];
            }

            return $result;
        });
    }

    /**
     * Subscription count by plan.
     */
    public function getSubscriptionsByPlan(): Collection
    {
        return Subscription::whereIn('status', ['active', 'trialing'])
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->selectRaw('plans.name as plan_name, COUNT(*) as count')
            ->groupBy('plans.name')
            ->get();
    }

    /**
     * Payment success rate: completed / total payments.
     */
    public function getPaymentSuccessRate(): float
    {
        $total     = Payment::count();
        $completed = Payment::where('payment_status', 'completed')->count();

        return $total > 0 ? round($completed / $total * 100, 1) : 0.0;
    }

    /**
     * Failed payments count in the last $days.
     */
    public function getFailedPayments(int $days = 30): int
    {
        return Payment::where('payment_status', 'failed')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * Summary array for the dashboard header KPIs.
     */
    public function getSummary(): array
    {
        $mrr = $this->getMrr();

        return [
            'mrr'                    => $mrr,
            'arr'                    => $mrr * 12,
            'mrr_formatted'          => '$' . number_format($mrr / 100, 2),
            'arr_formatted'          => '$' . number_format($mrr * 12 / 100, 2),
            'churn_rate'             => $this->getChurnRate(),
            'active_subscriptions'   => $this->getActiveCount(),
            'new_subscriptions_30d'  => $this->getNewSubscriptions(30),
            'total_revenue_30d'      => $this->getTotalRevenue(30),
            'payment_success_rate'   => $this->getPaymentSuccessRate(),
            'failed_payments_30d'    => $this->getFailedPayments(30),
        ];
    }
}
