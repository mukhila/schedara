<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Events\AI\AiContentGenerated;
use App\Models\AiGeneratedContent;

class ResponseSuggestionService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function generate(array $inputs, int $tenantId, int $userId, ?string $provider = null, ?string $model = null): array
    {
        $request = new AiRequestDTO(
            prompt:       $this->prompts->responseSuggestion($inputs),
            model:        '',
            systemPrompt: $this->prompts->system('You are an expert community manager skilled at crafting authentic, engaging social media responses.'),
            temperature:  0.7,
            maxTokens:    1200,
            jsonMode:     true,
        );

        $response  = $this->orchestrator->generate($request, 'response_suggestion', $tenantId, $userId, $provider, $model);
        $decoded   = $response->decoded();
        $responses = $decoded['responses'] ?? [];

        $record = AiGeneratedContent::create([
            'tenant_id'         => $tenantId,
            'user_id'           => $userId,
            'content_type'      => 'response_suggestion',
            'title'             => 'Response to: ' . \Illuminate\Support\Str::limit($inputs['original_message'] ?? '', 50),
            'generated_content' => $responses[0]['text'] ?? $response->content,
            'variations'        => $responses,
            'metadata'          => $inputs,
        ]);

        event(new AiContentGenerated($tenantId, $userId, 'response_suggestion', $record->id));

        return [
            'responses'   => $responses,
            'content_id'  => $record->uuid,
            'provider'    => $response->provider,
            'model'       => $response->model,
            'tokens_used' => $response->totalTokens,
        ];
    }
}
