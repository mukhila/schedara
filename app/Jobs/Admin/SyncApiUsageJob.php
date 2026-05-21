<?php

namespace App\Jobs\Admin;

use App\Models\ApiIntegration;
use App\Services\Admin\ApiIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncApiUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function handle(ApiIntegrationService $service): void
    {
        ApiIntegration::where('status', 'active')->each(function (ApiIntegration $integration) use ($service) {
            try {
                $service->healthCheck($integration);
            } catch (\Throwable $e) {
                Log::warning("SyncApiUsageJob failed for {$integration->provider_name}: {$e->getMessage()}");
            }
        });
    }
}
