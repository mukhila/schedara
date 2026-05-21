<?php

namespace App\Jobs\Billing;

use App\Mail\BillingInvoiceMail;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public readonly int $invoiceId)
    {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        $invoice = Invoice::with(['tenant', 'subscription.plan'])->find($this->invoiceId);

        if (! $invoice) {
            return;
        }

        $owner = $invoice->tenant->owner();

        if (! $owner) {
            return;
        }

        Mail::to($owner->email, $owner->name)->send(new BillingInvoiceMail($invoice));
    }
}
