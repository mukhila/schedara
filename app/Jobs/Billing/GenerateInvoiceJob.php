<?php

namespace App\Jobs\Billing;

use App\Models\Invoice;
use App\Services\Billing\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly int $invoiceId)
    {
        $this->onQueue('billing');
    }

    public function handle(InvoiceService $invoiceService): void
    {
        $invoice = Invoice::find($this->invoiceId);

        if (! $invoice) {
            return;
        }

        try {
            $invoiceService->generatePdf($invoice);
        } catch (\Throwable $e) {
            Log::error('Invoice PDF generation failed', [
                'invoice_id' => $this->invoiceId,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
