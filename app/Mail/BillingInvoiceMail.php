<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BillingInvoiceMail extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice #{$this->invoice->invoice_number} from " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.billing.invoice');
    }

    public function attachments(): array
    {
        if ($this->invoice->invoice_pdf && Storage::exists($this->invoice->invoice_pdf)) {
            return [
                Attachment::fromStorage($this->invoice->invoice_pdf)
                    ->as("invoice-{$this->invoice->invoice_number}.pdf")
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
