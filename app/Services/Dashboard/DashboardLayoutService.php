<?php

namespace App\Services\Dashboard;

use App\Models\DashboardLayout;
use Illuminate\Support\Facades\Cache;

class DashboardLayoutService
{
    public function get(int $userId, ?int $tenantId): DashboardLayout
    {
        return Cache::remember(
            "dashboard_layout:{$userId}:{$tenantId}",
            600,
            fn () => DashboardLayout::forUser($userId, $tenantId)
        );
    }

    public function save(int $userId, ?int $tenantId, array $order, array $hidden, array $config = []): DashboardLayout
    {
        $layout = DashboardLayout::forUser($userId, $tenantId);

        // Validate widget keys
        $valid  = DashboardLayout::defaultOrder();
        $order  = array_values(array_intersect($order, $valid));
        $hidden = array_values(array_intersect($hidden, $valid));

        // Ensure every widget key appears in order even if missing from request
        $missing = array_diff($valid, $order);
        $order   = array_merge($order, $missing);

        $layout->update([
            'widgets_order'  => $order,
            'widgets_hidden' => $hidden,
            'widgets_config' => $config ?: $layout->widgets_config,
        ]);

        Cache::forget("dashboard_layout:{$userId}:{$tenantId}");

        return $layout->fresh();
    }

    public function reset(int $userId, ?int $tenantId): DashboardLayout
    {
        $layout = DashboardLayout::forUser($userId, $tenantId);
        $layout->update([
            'widgets_order'  => DashboardLayout::defaultOrder(),
            'widgets_hidden' => [],
            'widgets_config' => null,
        ]);
        Cache::forget("dashboard_layout:{$userId}:{$tenantId}");
        return $layout->fresh();
    }

    public function toArray(DashboardLayout $layout): array
    {
        return [
            'order'   => $layout->orderedWidgets(),
            'hidden'  => $layout->hiddenWidgets(),
            'config'  => $layout->widgets_config ?? [],
        ];
    }
}
