<?php

namespace App\Services\Admin;

use App\Events\Admin\ApiQuotaExceeded;
use App\Models\AdminActivityLog;
use App\Models\ApiIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiIntegrationService
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return ApiIntegration::orderBy('display_name')->get();
    }

    public function create(array $data): ApiIntegration
    {
        $integration = ApiIntegration::create($data);

        AdminActivityLog::record('create', 'api', "Added API integration: {$integration->display_name}", $integration);

        return $integration;
    }

    public function update(ApiIntegration $integration, array $data): ApiIntegration
    {
        $integration->update($data);

        AdminActivityLog::record('update', 'api', "Updated API integration: {$integration->display_name}", $integration);

        return $integration->fresh();
    }

    public function delete(ApiIntegration $integration): void
    {
        AdminActivityLog::record('delete', 'api', "Deleted API integration: {$integration->display_name}", $integration);

        $integration->delete();
    }

    public function healthCheck(ApiIntegration $integration): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => 'Bearer ' . $integration->api_key])
                ->get($integration->metadata['health_endpoint'] ?? 'https://api.example.com/health');

            $status = $response->successful() ? 'active' : 'error';
            $error  = $response->successful() ? null : "HTTP {$response->status()}";
        } catch (\Throwable $e) {
            $status = 'error';
            $error  = $e->getMessage();
            Log::warning("API health check failed for {$integration->provider_name}: {$error}");
        }

        $integration->update([
            'status'         => $status,
            'last_checked_at' => now(),
            'last_error'     => $error,
        ]);

        return ['status' => $status, 'error' => $error];
    }

    public function incrementUsage(ApiIntegration $integration, int $units = 1): void
    {
        $integration->increment('current_usage', $units);

        if ($integration->isNearLimit() && $integration->usage_limit) {
            event(new ApiQuotaExceeded($integration));
        }
    }

    public function resetMonthlyUsage(): void
    {
        ApiIntegration::query()->update(['current_usage' => 0]);

        AdminActivityLog::record('reset_usage', 'api', 'Reset monthly API usage for all integrations');
    }

    public function getTotalMonthlyCost(): int
    {
        return (int) ApiIntegration::where('status', 'active')->sum('monthly_cost_cents');
    }
}
