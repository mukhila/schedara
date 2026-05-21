<?php

namespace App\Jobs\Client;

use App\Models\WhiteLabelSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateWhiteLabelAssetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly int $workspaceId,
    ) {
        $this->onQueue('media');
    }

    public function handle(): void
    {
        $settings = WhiteLabelSetting::where('client_workspace_id', $this->workspaceId)->first();

        if (!$settings) {
            return;
        }

        try {
            $this->generateCssFile($settings);
            Log::info("White-label assets generated for workspace {$this->workspaceId}");
        } catch (\Throwable $e) {
            Log::error('White-label asset generation failed', [
                'workspace_id' => $this->workspaceId,
                'error'        => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function generateCssFile(WhiteLabelSetting $settings): void
    {
        $css  = ":root { {$settings->cssVariables()} }";
        $path = "white-label/{$this->workspaceId}/theme.css";

        Storage::put($path, $css);
    }
}
