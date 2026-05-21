<?php

namespace App\Jobs\Analytics;

use App\Events\Analytics\AnalyticsUpdated;
use App\Events\Analytics\ViralPostDetected;
use App\Models\PostAnalytic;
use App\Models\SocialAccount;
use App\Services\Social\FacebookService;
use App\Services\Social\InstagramService;
use App\Services\Social\LinkedInService;
use App\Services\Social\PinterestService;
use App\Services\Social\ThreadsService;
use App\Services\Social\TikTokService;
use App\Services\Social\TwitterService;
use App\Services\Social\YouTubeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAnalyticsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(
        public readonly int $tenantId,
        public readonly ?int $accountId = null,
    ) {}

    public function uniqueId(): string
    {
        return "sync-analytics-{$this->tenantId}-{$this->accountId}";
    }

    public function handle(): void
    {
        $accounts = SocialAccount::where('tenant_id', $this->tenantId)
            ->when($this->accountId, fn ($q) => $q->where('id', $this->accountId))
            ->where('status', 'active')
            ->with('platform')
            ->get();

        foreach ($accounts as $account) {
            $this->syncAccount($account);
        }

        event(new AnalyticsUpdated($this->tenantId, 'post', []));
    }

    private function syncAccount(SocialAccount $account): void
    {
        $platform = $account->platform?->slug ?? '';

        $metrics = $this->fetchPlatformMetrics($platform, $account);

        foreach ($metrics as $metric) {
            $analytic = PostAnalytic::updateOrCreate(
                [
                    'post_id'           => $metric['post_id'] ?? null,
                    'social_account_id' => $account->id,
                    'platform'          => $platform,
                    'platform_post_id'  => $metric['platform_post_id'] ?? null,
                ],
                array_merge($metric, [
                    'tenant_id'  => $this->tenantId,
                    'fetched_at' => now(),
                ])
            );

            // Detect viral threshold (5% engagement rate)
            $er = $analytic->computedEngagementRate();
            if ($er >= config('analytics.viral_threshold', 5.0)) {
                event(new ViralPostDetected($analytic, $er));
            }
        }
    }

    private function fetchPlatformMetrics(string $platform, SocialAccount $account): array
    {
        $service = match ($platform) {
            'facebook'  => new FacebookService(),
            'instagram' => new InstagramService(),
            'twitter'   => new TwitterService(),
            'linkedin'  => new LinkedInService(),
            'tiktok'    => new TikTokService(),
            'youtube'   => new YouTubeService(),
            'threads'   => new ThreadsService(),
            'pinterest' => new PinterestService(),
            default     => null,
        };

        if (! $service) {
            return [];
        }

        try {
            return $service->fetchAnalytics($account);
        } catch (\Throwable $e) {
            Log::warning("Analytics fetch failed [{$platform}]", [
                'account_id' => $account->id,
                'error'      => $e->getMessage(),
            ]);
            return [];
        }
    }
}
