<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificationAnalyticsService
{
    private int $cacheTtl = 300; // 5 minutes

    public function deliveryStats(?int $tenantId = null, int $days = 30): array
    {
        $cacheKey = "notif_stats_{$tenantId}_{$days}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($tenantId, $days) {
            $base = NotificationLog::where('created_at', '>=', now()->subDays($days));

            if ($tenantId) {
                $base = $base->whereHas('notification', fn ($q) => $q->where('tenant_id', $tenantId));
            }

            $total     = (clone $base)->count();
            $sent      = (clone $base)->whereIn('delivery_status', ['sent', 'delivered'])->count();
            $failed    = (clone $base)->whereIn('delivery_status', ['failed', 'bounced'])->count();
            $pending   = (clone $base)->where('delivery_status', 'pending')->count();

            return [
                'total'         => $total,
                'sent'          => $sent,
                'failed'        => $failed,
                'pending'       => $pending,
                'delivery_rate' => $total > 0 ? round(($sent / $total) * 100, 1) : 0,
            ];
        });
    }

    public function channelBreakdown(?int $tenantId = null, int $days = 30): Collection
    {
        $cacheKey = "notif_channels_{$tenantId}_{$days}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($tenantId, $days) {
            return NotificationLog::select('channel', DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN delivery_status IN ('sent','delivered') THEN 1 ELSE 0 END) as delivered"),
                    DB::raw("SUM(CASE WHEN delivery_status IN ('failed','bounced') THEN 1 ELSE 0 END) as failed")
                )
                ->when($tenantId, fn ($q) => $q->whereHas('notification', fn ($q2) => $q2->where('tenant_id', $tenantId)))
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('channel')
                ->orderByDesc('total')
                ->get();
        });
    }

    public function recentFailures(?int $tenantId = null, int $limit = 10): Collection
    {
        return NotificationLog::with('notification')
            ->whereIn('delivery_status', ['failed', 'bounced'])
            ->when($tenantId, fn ($q) => $q->whereHas('notification', fn ($q2) => $q2->where('tenant_id', $tenantId)))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function dailyVolume(?int $tenantId = null, int $days = 14): Collection
    {
        return NotificationLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN delivery_status IN ('sent','delivered') THEN 1 ELSE 0 END) as delivered")
            )
            ->when($tenantId, fn ($q) => $q->whereHas('notification', fn ($q2) => $q2->where('tenant_id', $tenantId)))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();
    }
}
