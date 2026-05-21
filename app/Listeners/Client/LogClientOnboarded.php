<?php

namespace App\Listeners\Client;

use App\Events\Client\ClientOnboarded;
use App\Models\ClientActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogClientOnboarded implements ShouldQueue
{
    public function handle(ClientOnboarded $event): void
    {
        $workspace = $event->client->workspace;

        if ($workspace) {
            ClientActivityLog::create([
                'client_workspace_id' => $workspace->id,
                'user_id'             => null,
                'action'              => 'onboarding_completed',
                'module'              => 'onboarding',
                'description'         => "Client {$event->client->client_name} completed onboarding.",
                'created_at'          => now(),
            ]);
        }
    }
}
