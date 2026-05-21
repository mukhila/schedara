<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsAccount;
use App\Models\AnalyticsMetric;
use App\Models\SocialAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AnalyticsAccountService
{
    public function listForTenant(int $tenantId, bool $activeOnly = true): Collection
    {
        return AnalyticsAccount::forTenant($tenantId)
            ->when($activeOnly, fn ($q) => $q->active())
            ->with('socialAccount')
            ->get();
    }

    public function register(int $tenantId, SocialAccount $socialAccount): AnalyticsAccount
    {
        return AnalyticsAccount::firstOrCreate(
            [
                'tenant_id'         => $tenantId,
                'social_account_id' => $socialAccount->id,
            ],
            [
                'platform'           => $socialAccount->platform,
                'account_name'       => $socialAccount->account_name ?? null,
                'platform_account_id'=> $socialAccount->account_id ?? null,
                'is_active'          => true,
            ]
        );
    }

    public function deactivate(AnalyticsAccount $account): void
    {
        $account->update(['is_active' => false]);
    }

    public function upsertDailyMetric(AnalyticsAccount $account, string $date, array $data): AnalyticsMetric
    {
        $metric = AnalyticsMetric::updateOrCreate(
            [
                'analytics_account_id' => $account->id,
                'metric_date'          => $date,
            ],
            array_merge($data, [
                'tenant_id' => $account->tenant_id,
            ])
        );

        // Auto-compute engagement rate
        if (($metric->reach_count ?? 0) > 0 && ($metric->engagement_count ?? 0) > 0) {
            $metric->update(['engagement_rate' => $metric->computeEngagementRate()]);
        }

        $account->markSynced();

        return $metric;
    }

    public function metricsTimeSeries(int $tenantId, string $from, string $to, ?string $platform = null): array
    {
        return AnalyticsMetric::forTenant($tenantId)
            ->inRange($from, $to)
            ->when($platform, fn ($q) => $q->join('analytics_accounts', 'analytics_metrics.analytics_account_id', '=', 'analytics_accounts.id')
                ->where('analytics_accounts.platform', $platform))
            ->selectRaw('
                metric_date,
                SUM(impressions) AS impressions, SUM(reach_count) AS reach_count,
                SUM(engagement_count) AS engagement_count, SUM(likes) AS likes,
                SUM(comments) AS comments, SUM(shares) AS shares, SUM(saves) AS saves,
                SUM(clicks) AS clicks, SUM(conversions) AS conversions,
                SUM(new_followers) AS new_followers, SUM(unfollows) AS unfollows,
                SUM(revenue) AS revenue, SUM(spend) AS spend,
                AVG(engagement_rate) AS avg_engagement_rate
            ')
            ->groupBy('metric_date')
            ->orderBy('metric_date')
            ->get()
            ->toArray();
    }

    public function conversionSummary(int $tenantId, string $from, string $to): array
    {
        $row = AnalyticsMetric::forTenant($tenantId)
            ->inRange($from, $to)
            ->selectRaw('
                SUM(conversions)     AS total_conversions,
                SUM(clicks)          AS total_clicks,
                SUM(website_clicks)  AS total_website_clicks,
                SUM(revenue)         AS total_revenue,
                SUM(spend)           AS total_spend
            ')
            ->first();

        $conversions = (int) ($row->total_conversions ?? 0);
        $clicks      = (int) ($row->total_clicks ?? 0);
        $spend       = (float) ($row->total_spend ?? 0);
        $revenue     = (float) ($row->total_revenue ?? 0);

        return [
            'total_conversions'  => $conversions,
            'total_clicks'       => $clicks,
            'total_website_clicks'=> (int) ($row->total_website_clicks ?? 0),
            'conversion_rate'    => $clicks > 0 ? round($conversions / $clicks * 100, 4) : 0,
            'total_revenue'      => $revenue,
            'total_spend'        => $spend,
            'cpa'                => $conversions > 0 ? round($spend / $conversions, 4) : 0,
            'roi'                => $spend > 0 ? round(($revenue - $spend) / $spend * 100, 2) : 0,
        ];
    }
}
