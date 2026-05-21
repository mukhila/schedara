<?php

namespace App\Http\Controllers\Dashboard;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\DTOs\Analytics\DateRangeDTO;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function __construct(private DashboardExportService $exporter) {}

    public function pdf(Request $request): Response
    {
        return $this->exporter->exportPdf($this->filter($request));
    }

    public function excel(Request $request): Response
    {
        return $this->exporter->exportExcel($this->filter($request));
    }

    private function filter(Request $request): AnalyticsFilterDTO
    {
        $tenant = app('current.tenant');

        // Accept both date_from/date_to (from dashboard form) and from/to (direct API calls)
        $from = $request->input('date_from') ?? $request->input('from');
        $to   = $request->input('date_to')   ?? $request->input('to');

        $range = ($from && $to)
            ? new DateRangeDTO($from, $to)
            : DateRangeDTO::lastDays(30);

        $platforms = $request->has('platforms')
            ? explode(',', $request->input('platforms'))
            : null;

        return new AnalyticsFilterDTO(tenantId: $tenant->id, range: $range, platforms: $platforms);
    }
}
