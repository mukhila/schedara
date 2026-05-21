<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Events\AI\AiContentGenerated;
use App\Models\AiGeneratedContent;

class ContentIdeasService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function generate(array $inputs, int $tenantId, int $userId, ?string $provider = null, ?string $model = null): array
    {
        $request = new AiRequestDTO(
            prompt:       $this->prompts->contentIdeas($inputs),
            model:        '',
            systemPrompt: $this->prompts->system('You are a creative content strategist who specializes in viral social media content.'),
            temperature:  0.85,
            maxTokens:    2500,
            jsonMode:     true,
        );

        $response = $this->orchestrator->generate($request, 'content_ideas', $tenantId, $userId, $provider, $model);
        $decoded  = $response->decoded();
        $ideas    = $decoded['ideas'] ?? [];

        $record = AiGeneratedContent::create([
            'tenant_id'         => $tenantId,
            'user_id'           => $userId,
            'content_type'      => 'content_ideas',
            'platform'          => $inputs['platform'] ?? null,
            'title'             => 'Content Ideas: ' . ($inputs['industry'] ?? ''),
            'generated_content' => json_encode($ideas),
            'variations'        => $ideas,
            'metadata'          => array_merge($inputs, ['provider' => $response->provider]),
        ]);

        event(new AiContentGenerated($tenantId, $userId, 'content_ideas', $record->id));

        return [
            'ideas'       => $ideas,
            'content_id'  => $record->uuid,
            'provider'    => $response->provider,
            'model'       => $response->model,
            'tokens_used' => $response->totalTokens,
        ];
    }
}
