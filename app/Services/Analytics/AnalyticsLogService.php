<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnalyticsLogService
{
    public function record(
        int $tenantId,
        string $action,
        string $status = 'success',
        array $response = [],
        ?string $platform = null,
        ?string $errorMessage = null,
        ?int $durationMs = null,
    ): AnalyticsLog {
        return AnalyticsLog::record($tenantId, $action, $status, $response, $platform, $errorMessage, $durationMs);
    }

    public function timed(int $tenantId, string $action, callable $fn, ?string $platform = null): mixed
    {
        $start = microtime(true);
        try {
            $result = $fn();
            $this->record($tenantId, $action, 'success', [], $platform, null,
                (int) ((microtime(true) - $start) * 1000));
            return $result;
        } catch (\Throwable $e) {
            $this->record($tenantId, $action, 'error', [], $platform, $e->getMessage(),
                (int) ((microtime(true) - $start) * 1000));
            throw $e;
        }
    }

    public function recent(int $tenantId, int $limit = 50): LengthAwarePaginator
    {
        return AnalyticsLog::forTenant($tenantId)
            ->orderByDesc('created_at')
            ->paginate($limit);
    }

    public function errorRate(int $tenantId, int $days = 7): float
    {
        $total  = AnalyticsLog::forTenant($tenantId)->recent($days)->count();
        $errors = AnalyticsLog::forTenant($tenantId)->recent($days)->errors()->count();

        return $total > 0 ? round($errors / $total * 100, 2) : 0;
    }

    public function cleanup(int $daysOlderThan = 30): int
    {
        return AnalyticsLog::where('created_at', '<', now()->subDays($daysOlderThan))->delete();
    }
}
