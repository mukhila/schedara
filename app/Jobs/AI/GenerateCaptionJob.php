<?php

namespace App\Jobs\AI;

use App\Services\AI\CaptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCaptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        private readonly array  $inputs,
        private readonly int    $tenantId,
        private readonly int    $userId,
        private readonly ?string $provider = null,
        private readonly ?string $model    = null,
        private readonly ?string $callbackKey = null,   // Redis key to store result
    ) {
        $this->onQueue(config('ai.queue.name', 'ai'));
    }

    public function handle(CaptionService $service): void
    {
        $result = $service->generate($this->inputs, $this->tenantId, $this->userId, $this->provider, $this->model);

        if ($this->callbackKey) {
            \Illuminate\Support\Facades\Cache::put($this->callbackKey, $result, 3600);
        }
    }
}
