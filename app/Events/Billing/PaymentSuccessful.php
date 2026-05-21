<?php

namespace App\Events\Billing;

use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessful
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly ?Invoice     $invoice = null,
        public readonly int          $amountCents = 0,
    ) {}
}
