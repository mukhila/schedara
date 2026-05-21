<?php

namespace App\Jobs\Client;

use App\Mail\ClientReportReadyMail;
use App\Models\ClientReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $reportId,
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        $report = ClientReport::with(['workspace.client', 'workspace.whiteLabelSettings'])->find($this->reportId);

        if (! $report || ! $report->isReady()) {
            return;
        }

        $client   = $report->workspace->client;
        $branding = $report->workspace->whiteLabelSettings;

        try {
            Mail::to($client->email, $client->client_name)
                ->send(new ClientReportReadyMail($report, $client, $branding));

            Log::info('Client report email sent', ['report_id' => $this->reportId]);
        } catch (\Throwable $e) {
            Log::error('Failed to send client report email', [
                'report_id' => $this->reportId,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
