<?php

namespace App\Events\Admin;

use App\Models\ApiIntegration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiQuotaExceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ApiIntegration $integration) {}
}
