<?php

namespace App\Listeners\Client;

use App\Events\Client\PaymentCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateClientStatus implements ShouldQueue
{
    public function handle(PaymentCompleted $event): void
    {
        if ($event->client->status === 'suspended') {
            $event->client->update(['status' => 'active']);
        }
    }
}
