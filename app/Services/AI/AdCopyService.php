<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Events\AI\AiContentGenerated;
use App\Models\AiGeneratedContent;

class AdCopyService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function generate(array $inputs, int $tenantId, int $userId, ?string $provider = null, ?string $model = null): array
    {
        $brandVoice = !empty($inputs['brand_voice_id'])
            ? \App\Models\AiBrandVoice::where('uuid', $inputs['brand_voice_id'])->first()
            : null;

        $request = new AiRequestDTO(
            prompt:       $this->prompts->adCopy($inputs),
            model:        '',
            systemPrompt: $this->prompts->system('You are a direct-response copywriter specializing in paid advertising with a track record of high-converting ad campaigns.', $brandVoice),
            temperature:  0.75,
            maxTokens:    2000,
            jsonMode:     true,
        );

        $response   = $this->orchestrator->generate($request, 'ad_copy', $tenantId, $userId, $provider, $model);
        $decoded    = $response->decoded();
        $variations = $decoded['variations'] ?? [];

        $record = AiGeneratedContent::create([
            'tenant_id'         => $tenantId,
            'user_id'           => $userId,
            'content_type'      => 'ad_copy',
            'platform'          => $inputs['platform'] ?? null,
            'title'             => 'Ad Copy: ' . ($inputs['product'] ?? ''),
            'generated_content' => $variations[0]['primary_text'] ?? $response->content,
            'variations'        => $variations,
            'metadata'          => array_merge($inputs, ['provider' => $response->provider]),
        ]);

        event(new AiContentGenerated($tenantId, $userId, 'ad_copy', $record->id));

        return [
            'variations'  => $variations,
            'content_id'  => $record->uuid,
            'provider'    => $response->provider,
            'model'       => $response->model,
            'tokens_used' => $response->totalTokens,
        ];
    }
}
