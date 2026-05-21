<?php

namespace App\Jobs\Client;

use App\Models\AnalyticsAccount;
use App\Models\AnalyticsMetric;
use App\Models\ClientReport;
use App\Models\ClientWorkspace;
use App\Models\PostAnalytic;
use App\Repositories\Contracts\ClientReportRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateClientReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(
        public readonly int $reportId,
    ) {
        $this->onQueue('reports');
    }

    public function handle(ClientReportRepositoryInterface $repo): void
    {
        $report = ClientReport::with(['workspace.client', 'workspace.whiteLabelSettings'])->find($this->reportId);

        if (!$report) {
            return;
        }

        $report->update(['status' => 'generating']);

        try {
            $data     = $this->collectReportData($report);
            $filePath = $this->renderReport($report, $data);
            $fileUrl  = Storage::url($filePath);

            $repo->markReady($report, $filePath, $fileUrl);

            if ($report->email_delivery) {
                SendReportEmailJob::dispatch($report->id);
            }
        } catch (\Throwable $e) {
            Log::error('Client report generation failed', [
                'report_id' => $this->reportId,
                'error'     => $e->getMessage(),
            ]);

            $repo->markFailed($report, $e->getMessage());
        }
    }

    private function collectReportData(ClientReport $report): array
    {
        $workspace = $report->workspace;
        $config    = $report->report_config ?? [];

        return [
            'workspace'    => $workspace,
            'client'       => $workspace->client,
            'branding'     => $workspace->whiteLabelSettings,
            'period_start' => $config['period_start'] ?? now()->subMonth()->toDateString(),
            'period_end'   => $config['period_end'] ?? now()->toDateString(),
            'metrics'      => $this->getMetrics($workspace, $config),
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    private function getMetrics(ClientWorkspace $workspace, array $config): array
    {
        $tenantId = $workspace->client->agency_id;
        $from     = $config['period_start'] ?? now()->subMonth()->toDateString();
        $to       = $config['period_end'] ?? now()->toDateString();
        $fromDt   = $from . ' 00:00:00';
        $toDt     = $to . ' 23:59:59';

        $kpis = PostAnalytic::where('tenant_id', $tenantId)
            ->whereBetween('fetched_at', [$fromDt, $toDt])
            ->selectRaw('
                COUNT(*) AS total_posts,
                COALESCE(SUM(reach), 0) AS total_reach,
                COALESCE(SUM(likes + comments + shares + saves), 0) AS total_engagement
            ')
            ->first();

        $analyticsAccountIds = AnalyticsAccount::where('tenant_id', $tenantId)->pluck('id');
        $followersGained = AnalyticsMetric::whereIn('analytics_account_id', $analyticsAccountIds)
            ->whereBetween('metric_date', [$from, $to])
            ->sum('new_followers');

        $topPosts = PostAnalytic::where('tenant_id', $tenantId)
            ->whereBetween('fetched_at', [$fromDt, $toDt])
            ->whereNotNull('post_id')
            ->with('post:id,uuid,content,platform,scheduled_at')
            ->orderByDesc('engagement_rate')
            ->limit(5)
            ->get()
            ->map(fn ($a) => [
                'content'    => $a->post?->content,
                'platform'   => $a->platform,
                'reach'      => $a->reach,
                'engagement' => $a->likes + $a->comments + $a->shares + $a->saves,
            ])
            ->toArray();

        $platformBreakdown = PostAnalytic::where('tenant_id', $tenantId)
            ->whereBetween('fetched_at', [$fromDt, $toDt])
            ->selectRaw('
                platform,
                COUNT(*) AS posts,
                COALESCE(SUM(reach), 0) AS reach,
                COALESCE(SUM(likes + comments + shares + saves), 0) AS engagement,
                COALESCE(AVG(engagement_rate), 0) AS engagement_rate
            ')
            ->groupBy('platform')
            ->orderByDesc('reach')
            ->get()
            ->toArray();

        return [
            'total_posts'        => (int) ($kpis->total_posts ?? 0),
            'total_reach'        => (int) ($kpis->total_reach ?? 0),
            'total_engagement'   => (int) ($kpis->total_engagement ?? 0),
            'followers_gained'   => (int) $followersGained,
            'top_posts'          => $topPosts,
            'platform_breakdown' => $platformBreakdown,
        ];
    }

    private function renderReport(ClientReport $report, array $data): string
    {
        $path = "client-reports/{$report->uuid}.pdf";

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.client.pdf', $data)->setPaper('a4');
            Storage::put($path, $pdf->output());
        } else {
            // HTML fallback until dompdf is installed
            $html = view('reports.client.pdf', $data)->render();
            Storage::put($path, $html);
        }

        return $path;
    }
}
