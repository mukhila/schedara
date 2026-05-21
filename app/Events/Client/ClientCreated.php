<?php

namespace App\Events\Client;

use App\Models\AgencyClient;
use App\Models\ClientWorkspace;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly AgencyClient    $client,
        public readonly ClientWorkspace $workspace,
    ) {}
}
