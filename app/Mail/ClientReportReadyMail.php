<?php

namespace App\Mail;

use App\Models\AgencyClient;
use App\Models\ClientReport;
use App\Models\WhiteLabelSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientReportReadyMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly ClientReport $report,
        public readonly AgencyClient $client,
        public readonly ?WhiteLabelSetting $branding = null,
    ) {}

    public function envelope(): Envelope
    {
        $sender = $this->branding?->brand_name ?? config('app.name');

        return new Envelope(subject: "{$sender}: Your Analytics Report is Ready");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client.report',
            with: ['branding' => $this->branding],
        );
    }

    public function attachments(): array
    {
        if (! $this->report->file_path) {
            return [];
        }

        $abs = storage_path('app/' . $this->report->file_path);

        if (! file_exists($abs)) {
            return [];
        }

        return [
            Attachment::fromPath($abs)
                ->as($this->report->report_name . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
