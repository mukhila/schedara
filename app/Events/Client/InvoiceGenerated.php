<?php

namespace App\Events\Client;

use App\Models\AgencyClient;
use App\Models\ClientBilling;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ClientBilling $invoice,
        public readonly AgencyClient  $client,
    ) {}
}
