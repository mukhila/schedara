<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\AnalyticsMetric;
use App\Models\AnalyticsReport;
use Illuminate\Support\Facades\Storage;

class AnalyticsExportService
{
    public function __construct(
        private AnalyticsDashboardService $dashboard,
        private EngagementAnalyticsService $engagement,
        private RoiCalculationService $roi,
    ) {}

    public function generateCsv(AnalyticsReport $report): string
    {
        $summary = $this->buildSummaryData($report);
        $rows    = $this->buildCsvRows($report, $summary);

        $disk     = config('analytics.report_disk', 'local');
        $dir      = config('analytics.report_path', 'analytics/reports');
        $filename = $dir . '/' . $report->uuid . '.csv';

        $csv = $this->arrayToCsv($rows);
        Storage::disk($disk)->put($filename, $csv);

        return Storage::disk($disk)->url($filename);
    }

    public function generateMetricsCsv(int $tenantId, string $from, string $to): string
    {
        $rows = AnalyticsMetric::forTenant($tenantId)
            ->inRange($from, $to)
            ->join('analytics_accounts', 'analytics_metrics.analytics_account_id', '=', 'analytics_accounts.id')
            ->select([
                'analytics_metrics.metric_date',
                'analytics_accounts.platform',
                'analytics_accounts.account_name',
                'analytics_metrics.impressions',
                'analytics_metrics.reach_count',
                'analytics_metrics.engagement_count',
                'analytics_metrics.likes',
                'analytics_metrics.comments',
                'analytics_metrics.shares',
                'analytics_metrics.saves',
                'analytics_metrics.clicks',
                'analytics_metrics.conversions',
                'analytics_metrics.followers',
                'analytics_metrics.new_followers',
                'analytics_metrics.unfollows',
                'analytics_metrics.revenue',
                'analytics_metrics.spend',
                'analytics_metrics.engagement_rate',
            ])
            ->orderBy('analytics_metrics.metric_date')
            ->get();

        $headers = [
            'Date', 'Platform', 'Account', 'Impressions', 'Reach', 'Engagement',
            'Likes', 'Comments', 'Shares', 'Saves', 'Clicks', 'Conversions',
            'Followers', 'New Followers', 'Unfollows', 'Revenue', 'Spend', 'Engagement Rate %',
        ];

        $csv = $this->arrayToCsv(
            array_merge(
                [$headers],
                $rows->map(fn ($r) => [
                    $r->metric_date, $r->platform, $r->account_name ?? '',
                    $r->impressions, $r->reach_count, $r->engagement_count,
                    $r->likes, $r->comments, $r->shares, $r->saves,
                    $r->clicks, $r->conversions, $r->followers, $r->new_followers, $r->unfollows,
                    number_format($r->revenue, 2), number_format($r->spend, 2),
                    number_format($r->engagement_rate, 4),
                ])->toArray()
            )
        );

        $disk     = config('analytics.report_disk', 'local');
        $filename = config('analytics.report_path', 'analytics/reports')
            . '/metrics-' . $tenantId . '-' . $from . '-' . $to . '.csv';

        Storage::disk($disk)->put($filename, $csv);

        return Storage::disk($disk)->url($filename);
    }

    private function buildSummaryData(AnalyticsReport $report): array
    {
        $filter = new AnalyticsFilterDTO(
            tenantId: $report->tenant_id,
            range:    new \App\DTOs\Analytics\DateRangeDTO(
                $report->date_from->toDateString(),
                $report->date_to->toDateString()
            ),
            platforms: $report->filters['platforms'] ?? null,
        );

        return match ($report->type) {
            'engagement' => $this->engagement->summary($filter),
            'roi'        => $this->roi->summary($filter),
            default      => $this->dashboard->overview($filter),
        };
    }

    private function buildCsvRows(AnalyticsReport $report, array $summary): array
    {
        $rows = [['Schedara Analytics Report — ' . $report->name], []];

        $rows[] = ['Report type',  ucfirst($report->type)];
        $rows[] = ['Date range',   $report->date_from->format('M d, Y') . ' – ' . $report->date_to->format('M d, Y')];
        $rows[] = ['Generated at', now()->toDateTimeString()];
        $rows[] = [];

        // KPI section
        if (!empty($summary['kpi'])) {
            $rows[] = ['Metric', 'Value'];
            foreach ($summary['kpi'] as $key => $value) {
                $rows[] = [ucwords(str_replace('_', ' ', $key)), $value];
            }
        }

        return $rows;
    }

    private function arrayToCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }
}
