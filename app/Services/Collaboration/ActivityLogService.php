<?php

namespace App\Services\Collaboration;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogService
{
    public function forTenant(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        return ActivityLog::forTenant($tenantId)
            ->when(isset($filters['user_id']),  fn ($q) => $q->forUser($filters['user_id']))
            ->when(isset($filters['module']),   fn ($q) => $q->module($filters['module']))
            ->when(isset($filters['action']),   fn ($q) => $q->where('action', $filters['action']))
            ->when(isset($filters['from']) || isset($filters['to']),
                fn ($q) => $q->dateRange($filters['from'] ?? null, $filters['to'] ?? null)
            )
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 30);
    }

    public function recentForTenant(int $tenantId, int $limit = 20): Collection
    {
        return ActivityLog::forTenant($tenantId)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function forUser(int $userId, int $tenantId, int $perPage = 20): LengthAwarePaginator
    {
        return ActivityLog::forTenant($tenantId)
            ->forUser($userId)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function availableModules(int $tenantId): array
    {
        return ActivityLog::forTenant($tenantId)
            ->distinct()
            ->pluck('module')
            ->sort()
            ->values()
            ->all();
    }

    public function pruneOlderThan(int $tenantId, int $days = 90): int
    {
        return ActivityLog::forTenant($tenantId)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
