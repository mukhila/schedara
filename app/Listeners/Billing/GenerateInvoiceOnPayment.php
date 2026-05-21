<?php

namespace App\Listeners\Billing;

use App\Events\Billing\PaymentSuccessful;
use App\Jobs\Billing\GenerateInvoiceJob;

class GenerateInvoiceOnPayment
{
    public function handle(PaymentSuccessful $event): void
    {
        if ($event->invoice) {
            GenerateInvoiceJob::dispatch($event->invoice->id);
        }
    }
}
