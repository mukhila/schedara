<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Events\Analytics\CampaignCompleted;
use App\Events\Analytics\ROIThresholdReached;
use App\Models\AnalyticsCampaign;
use App\Repositories\Analytics\AnalyticsCampaignRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CampaignAnalyticsService
{
    public function __construct(private AnalyticsCampaignRepository $repo) {}

    public function list(AnalyticsFilterDTO $f, int $perPage = 20)
    {
        return $this->repo->paginate($f, $perPage);
    }

    public function summary(AnalyticsFilterDTO $f): array
    {
        $cacheKey = "analytics:campaigns:summary:{$f->tenantId}:{$f->range->fromString()}:{$f->range->toString()}";

        return Cache::remember($cacheKey, 300, fn () =>
            $this->repo->summary($f->tenantId, $f->range->fromString(), $f->range->toString())
        );
    }

    public function create(int $tenantId, int $userId, array $data): AnalyticsCampaign
    {
        return AnalyticsCampaign::create([
            'uuid'        => (string) Str::uuid(),
            'tenant_id'   => $tenantId,
            'created_by'  => $userId,
            'name'        => $data['name'],
            'platform'    => $data['platform'] ?? null,
            'status'      => $data['status'] ?? 'draft',
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'] ?? null,
            'budget'      => $data['budget'] ?? 0,
            'tags'        => $data['tags'] ?? null,
        ]);
    }

    public function updateMetrics(AnalyticsCampaign $campaign, array $metrics): AnalyticsCampaign
    {
        $spend   = $metrics['spend'] ?? $campaign->spend;
        $revenue = $metrics['revenue'] ?? $campaign->revenue;
        $clicks  = (int) ($metrics['clicks'] ?? $campaign->clicks);

        $roi  = $spend > 0 ? round(($revenue - $spend) / $spend * 100, 4) : 0;
        $roas = $spend > 0 ? round($revenue / $spend, 4) : 0;
        $ctr  = ($metrics['impressions'] ?? 0) > 0
            ? round($clicks / $metrics['impressions'] * 100, 4) : 0;
        $cpc  = $clicks > 0 ? round($spend / $clicks, 4) : 0;
        $cpm  = ($metrics['impressions'] ?? 0) >= 1000
            ? round($spend / ($metrics['impressions'] / 1000), 4) : 0;

        $campaign->update(array_merge($metrics, compact('roi', 'roas', 'ctr', 'cpc', 'cpm')));

        // Dispatch events
        if ($campaign->status === 'completed') {
            event(new CampaignCompleted($campaign));
        }

        if ($roi > config('analytics.roi_alert_threshold', 200)) {
            event(new ROIThresholdReached($campaign, $roi));
        }

        return $campaign->fresh();
    }

    public function markCompleted(AnalyticsCampaign $campaign): void
    {
        $campaign->update(['status' => 'completed']);
        event(new CampaignCompleted($campaign));
    }

    public function topPerformers(int $tenantId, int $limit = 5): array
    {
        return $this->repo->performingByROI($tenantId, $limit)->toArray();
    }
}
