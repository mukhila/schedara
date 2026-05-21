<?php

namespace App\Events\Client;

use App\Models\ClientWorkspace;
use App\Models\WhiteLabelSetting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteLabelUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly WhiteLabelSetting $settings,
        public readonly ClientWorkspace   $workspace,
    ) {}
}
