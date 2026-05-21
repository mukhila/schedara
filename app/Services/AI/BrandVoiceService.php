<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Models\AiBrandVoice;

class BrandVoiceService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function analyze(array $inputs, int $tenantId, int $userId): array
    {
        $request = new AiRequestDTO(
            prompt:       $this->prompts->brandVoiceAnalysis($inputs),
            model:        '',
            systemPrompt: $this->prompts->system('You are a brand strategist who analyzes content to extract the unique voice and personality of a brand.'),
            temperature:  0.3,
            maxTokens:    1500,
            jsonMode:     true,
        );

        $response = $this->orchestrator->generate($request, 'brand_voice', $tenantId, $userId);
        return $response->decoded() ?: [];
    }

    public function forTenant(int $tenantId)
    {
        return AiBrandVoice::forTenant($tenantId)->orderByDesc('is_default')->get();
    }

    public function default(int $tenantId): ?AiBrandVoice
    {
        return AiBrandVoice::forTenant($tenantId)->where('is_default', true)->first();
    }

    public function create(int $tenantId, array $data): AiBrandVoice
    {
        return AiBrandVoice::create(array_merge($data, ['tenant_id' => $tenantId]));
    }

    public function update(AiBrandVoice $voice, array $data): AiBrandVoice
    {
        $voice->update($data);
        return $voice->fresh();
    }
}
