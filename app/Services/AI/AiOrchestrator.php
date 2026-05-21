<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\DTOs\AI\AiResponseDTO;
use App\Events\AI\AiLimitReached;
use App\Events\AI\AiProviderFailed;
use App\Exceptions\AI\UsageLimitExceededException;
use App\Models\AiLog;
use App\Models\AiRequest;
use App\Models\AiUsageLimit;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AiOrchestrator
{
    public function __construct(
        private readonly UsageService $usage,
    ) {}

    /**
     * Execute an AI request with automatic failover, logging, and usage tracking.
     */
    public function generate(
        AiRequestDTO $request,
        string       $requestType,
        int          $tenantId,
        int          $userId,
        string       $preferredProvider = null,
        ?string      $model             = null,
    ): AiResponseDTO {
        // Check usage limit before making any API call
        if ($this->usage->isOverLimit($tenantId)) {
            event(new AiLimitReached($tenantId, $userId));
            throw new UsageLimitExceededException();
        }

        $provider = $preferredProvider ?? config('ai.default_provider', 'openai');
        $failover = config('ai.failover.enabled', true);

        // Resolve the model for the chosen provider
        $resolvedRequest = $this->resolveModel($request, $provider, $model);

        // Create a pending log record
        $record = AiRequest::create([
            'tenant_id'    => $tenantId,
            'user_id'      => $userId,
            'ai_provider'  => $provider,
            'ai_model'     => $resolvedRequest->model,
            'request_type' => $requestType,
            'prompt'       => $resolvedRequest->prompt,
            'system_prompt'=> $resolvedRequest->systemPrompt,
            'status'       => 'processing',
        ]);

        $tried    = [];
        $lastError = null;

        $providerOrder = $failover
            ? array_unique(array_merge([$provider], AiProviderFactory::failoverOrder()))
            : [$provider];

        foreach ($providerOrder as $p) {
            if (in_array($p, $tried, true)) continue;
            $tried[] = $p;

            $instance = AiProviderFactory::make($p);
            if (!$instance->isConfigured()) continue;

            $req = $this->resolveModel($request, $p, $p === $provider ? $model : null);

            $attemptStart = hrtime(true);

            try {
                $response = $instance->complete($req);
                $ms       = (int) ((hrtime(true) - $attemptStart) / 1_000_000);

                $record->markCompleted(
                    $response->content,
                    $response->inputTokens,
                    $response->outputTokens,
                    $response->costEstimate,
                    $response->processingTimeMs,
                );
                $record->update(['ai_provider' => $p, 'ai_model' => $req->model]);

                AiLog::record(
                    $record->id, 'completed', 'success',
                    $p, $req->model, null,
                    $response->totalTokens, $ms,
                );

                $this->usage->track($tenantId, $p, $response->totalTokens, $response->costEstimate);

                return $response;
            } catch (Throwable $e) {
                $ms        = (int) ((hrtime(true) - $attemptStart) / 1_000_000);
                $lastError = $e;

                AiLog::record(
                    $record->id,
                    count($tried) > 1 ? 'failover' : 'attempt',
                    'error',
                    $p, $req->model, null, 0, $ms,
                    $e->getMessage(),
                );

                Log::warning("AI provider [{$p}] failed for request #{$record->id}: " . $e->getMessage());
                event(new AiProviderFailed($tenantId, $p, $requestType, $e->getMessage()));

                if (!$failover) break;
            }
        }

        $record->markFailed($lastError?->getMessage() ?? 'Unknown error');
        AiLog::record($record->id, 'failed', 'error', null, null, null, 0, null, $lastError?->getMessage());
        throw new RuntimeException("All AI providers failed. Last error: " . $lastError?->getMessage());
    }

    private function resolveModel(AiRequestDTO $request, string $provider, ?string $model): AiRequestDTO
    {
        if ($model) {
            return $request->withModel($model);
        }

        $defaultModel = AiProviderFactory::make($provider)->getDefaultModel();
        return $request->withModel($defaultModel);
    }
}
