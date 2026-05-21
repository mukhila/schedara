<?php

namespace App\Jobs\Client;

use App\Models\ClientBilling;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $invoiceId,
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        $invoice = ClientBilling::with('client')->find($this->invoiceId);

        if (!$invoice) {
            return;
        }

        try {
            Mail::send('emails.client.invoice', ['invoice' => $invoice], function ($m) use ($invoice) {
                $m->to($invoice->client->email, $invoice->client->client_name)
                  ->subject("Invoice #{$invoice->invoice_number} from your agency");
            });

            Log::info('Invoice email sent', ['invoice_id' => $this->invoiceId]);
        } catch (\Throwable $e) {
            Log::error('Failed to send invoice email', [
                'invoice_id' => $this->invoiceId,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
