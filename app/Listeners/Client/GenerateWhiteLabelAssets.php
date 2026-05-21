<?php

namespace App\Listeners\Client;

use App\Events\Client\WhiteLabelUpdated;
use App\Jobs\Client\GenerateWhiteLabelAssetsJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateWhiteLabelAssets implements ShouldQueue
{
    public function handle(WhiteLabelUpdated $event): void
    {
        GenerateWhiteLabelAssetsJob::dispatch($event->workspace->id);
    }
}
