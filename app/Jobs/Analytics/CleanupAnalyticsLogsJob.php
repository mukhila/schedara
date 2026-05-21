<?php

namespace App\Jobs\Analytics;

use App\Models\AnalyticsLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupAnalyticsLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300;

    public function __construct(public readonly int $daysOlderThan = 30) {}

    public function handle(): void
    {
        AnalyticsLog::where('created_at', '<', now()->subDays($this->daysOlderThan))->delete();
    }
}
