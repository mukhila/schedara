<?php

namespace App\Listeners\Client;

use App\Events\Client\InvoiceGenerated;
use App\Jobs\Client\SendInvoiceJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoiceEmail implements ShouldQueue
{
    public string $queue = 'emails';

    public function handle(InvoiceGenerated $event): void
    {
        SendInvoiceJob::dispatch($event->invoice->id);
    }
}
