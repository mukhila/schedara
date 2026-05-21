<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Events\AI\AiContentGenerated;
use App\Models\AiGeneratedContent;

class SeoService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function optimize(array $inputs, int $tenantId, int $userId, ?string $provider = null, ?string $model = null): array
    {
        $request = new AiRequestDTO(
            prompt:       $this->prompts->seoOptimize($inputs),
            model:        '',
            systemPrompt: $this->prompts->system('You are an SEO expert who specializes in social media, content marketing, and copywriting optimization.'),
            temperature:  0.4,   // Lower temp for analytical tasks
            maxTokens:    2000,
            jsonMode:     true,
        );

        $response = $this->orchestrator->generate($request, 'seo', $tenantId, $userId, $provider, $model);
        $decoded  = $response->decoded();

        $record = AiGeneratedContent::create([
            'tenant_id'         => $tenantId,
            'user_id'           => $userId,
            'content_type'      => 'seo',
            'platform'          => $inputs['platform'] ?? null,
            'title'             => 'SEO Analysis',
            'generated_content' => $decoded['optimized_content'] ?? $response->content,
            'metadata'          => array_merge($decoded, ['original' => $inputs['content'] ?? '']),
        ]);

        event(new AiContentGenerated($tenantId, $userId, 'seo', $record->id));

        return array_merge($decoded, [
            'content_id'  => $record->uuid,
            'provider'    => $response->provider,
            'model'       => $response->model,
            'tokens_used' => $response->totalTokens,
        ]);
    }
}
