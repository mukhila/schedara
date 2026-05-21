<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Events\AI\AiContentGenerated;
use App\Models\AiGeneratedContent;

class HashtagService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function generate(array $inputs, int $tenantId, int $userId, ?string $provider = null, ?string $model = null): array
    {
        $request = new AiRequestDTO(
            prompt:       $this->prompts->hashtags($inputs),
            model:        '',
            systemPrompt: $this->prompts->system('You are a hashtag strategy expert who knows current trends on all social platforms.'),
            temperature:  0.6,
            maxTokens:    1500,
            jsonMode:     true,
        );

        $response = $this->orchestrator->generate($request, 'hashtag', $tenantId, $userId, $provider, $model);
        $decoded  = $response->decoded();
        $hashtags = $decoded['hashtags'] ?? [];

        $record = AiGeneratedContent::create([
            'tenant_id'         => $tenantId,
            'user_id'           => $userId,
            'content_type'      => 'hashtag',
            'platform'          => $inputs['platform'] ?? null,
            'title'             => 'Hashtags: ' . ($inputs['topic'] ?? ''),
            'generated_content' => implode(' ', array_column($hashtags, 'tag')),
            'variations'        => $hashtags,
            'metadata'          => ['topic' => $inputs['topic'] ?? '', 'provider' => $response->provider],
        ]);

        event(new AiContentGenerated($tenantId, $userId, 'hashtag', $record->id));

        return [
            'hashtags'    => $hashtags,
            'content_id'  => $record->uuid,
            'provider'    => $response->provider,
            'model'       => $response->model,
            'tokens_used' => $response->totalTokens,
        ];
    }
}
