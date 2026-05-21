<?php

namespace App\Services\Dashboard;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Services\Analytics\AnalyticsDashboardService;
use App\Services\Analytics\RoiCalculationService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class DashboardExportService
{
    public function __construct(
        private AnalyticsDashboardService $dashboard,
        private RoiCalculationService     $roi,
        private PostPerformanceService    $postPerf,
    ) {}

    // ── PDF ───────────────────────────────────────────────────────

    public function exportPdf(AnalyticsFilterDTO $filter): Response
    {
        $data = $this->buildData($filter);

        // Use barryvdh/laravel-dompdf if installed
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('backend.dashboard.export', $data)
                ->setPaper('a4', 'landscape');
            return response($pdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="dashboard-' . now()->format('Y-m-d') . '.pdf"',
            ]);
        }

        // Fallback: printable HTML page (user can print → save as PDF from browser)
        $html = View::make('backend.dashboard.export', $data)->render();
        return response($html, 200, [
            'Content-Type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="dashboard-' . now()->format('Y-m-d') . '.html"',
        ]);
    }

    // ── Excel ─────────────────────────────────────────────────────

    public function exportExcel(AnalyticsFilterDTO $filter): Response
    {
        $data = $this->buildData($filter);

        // Use maatwebsite/laravel-excel if installed
        if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\DashboardExport($data),
                'dashboard-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        // Fallback: CSV
        return $this->exportCsv($data, $filter);
    }

    // ── CSV fallback ──────────────────────────────────────────────

    private function exportCsv(array $data, AnalyticsFilterDTO $filter): Response
    {
        $rows = [];

        $rows[] = ['Schedara Dashboard Export', '', '', '', ''];
        $rows[] = ['Date range', $filter->range->fromString(), 'to', $filter->range->toString(), ''];
        $rows[] = ['Generated at', now()->toDateTimeString(), '', '', ''];
        $rows[] = [];

        // KPI section
        $rows[] = ['KPI Summary'];
        $rows[] = ['Metric', 'Value'];
        foreach ($data['kpi'] ?? [] as $k => $v) {
            $rows[] = [ucwords(str_replace('_', ' ', $k)), $v];
        }
        $rows[] = [];

        // Engagement time series
        $rows[] = ['Engagement Time Series'];
        $rows[] = ['Date', 'Impressions', 'Reach', 'Engagement', 'Likes', 'Comments', 'Shares', 'Saves'];
        foreach ($data['timeSeries'] ?? [] as $row) {
            $rows[] = [
                $row['period']           ?? '',
                $row['impressions']      ?? 0,
                $row['reach']            ?? 0,
                ($row['likes'] ?? 0) + ($row['comments'] ?? 0) + ($row['shares'] ?? 0) + ($row['saves'] ?? 0),
                $row['likes']            ?? 0,
                $row['comments']         ?? 0,
                $row['shares']           ?? 0,
                $row['saves']            ?? 0,
            ];
        }
        $rows[] = [];

        // Follower summary
        $rows[] = ['Follower Summary'];
        foreach ($data['followerKpi'] ?? [] as $k => $v) {
            $rows[] = [ucwords(str_replace('_', ' ', $k)), $v];
        }
        $rows[] = [];

        // Revenue summary
        $rows[] = ['Revenue Summary'];
        foreach ($data['roiSummary'] ?? [] as $k => $v) {
            if (!is_array($v)) {
                $rows[] = [ucwords(str_replace('_', ' ', $k)), $v];
            }
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, (array) $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="dashboard-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ── Data builder ──────────────────────────────────────────────

    private function buildData(AnalyticsFilterDTO $filter): array
    {
        $overview   = $this->dashboard->overview($filter);
        $roiSummary = $this->roi->summary($filter);

        return [
            'filter'      => $filter,
            'kpi'         => $overview['kpi']         ?? [],
            'followerKpi' => $overview['followers']    ?? [],
            'timeSeries'  => $overview['time_series']  ?? [],
            'byPlatform'  => $overview['by_platform']  ?? [],
            'campaigns'   => $overview['campaigns']    ?? [],
            'dateRange'   => $overview['date_range']   ?? [
                'from' => $filter->range->fromString(),
                'to'   => $filter->range->toString(),
                'days' => $filter->range->diffInDays(),
            ],
            'roiSummary'  => $roiSummary,
            'postPerf'    => $this->postPerf->get($filter),
        ];
    }
}
