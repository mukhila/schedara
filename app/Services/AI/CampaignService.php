<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Events\AI\AiContentGenerated;
use App\Events\AI\CampaignGenerated;
use App\Models\AiGeneratedContent;

class CampaignService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function generate(array $inputs, int $tenantId, int $userId, ?string $provider = null, ?string $model = null): array
    {
        // Campaigns are complex — use a capable model and allow more tokens
        $resolvedProvider = $provider ?? 'claude';  // Default to Claude for long-form
        $resolvedModel    = $model    ?? null;

        $request = new AiRequestDTO(
            prompt:       $this->prompts->campaign($inputs),
            model:        '',
            systemPrompt: $this->prompts->system('You are a senior marketing strategist with expertise in multi-platform social media campaigns.'),
            temperature:  0.7,
            maxTokens:    4000,
            jsonMode:     true,
        );

        $response = $this->orchestrator->generate($request, 'campaign', $tenantId, $userId, $resolvedProvider, $resolvedModel);
        $campaign = $response->decoded();

        $record = AiGeneratedContent::create([
            'tenant_id'         => $tenantId,
            'user_id'           => $userId,
            'content_type'      => 'campaign',
            'title'             => $campaign['campaign_name'] ?? ('Campaign: ' . ($inputs['product'] ?? '')),
            'generated_content' => json_encode($campaign),
            'metadata'          => array_merge($inputs, ['provider' => $response->provider, 'model' => $response->model]),
        ]);

        event(new AiContentGenerated($tenantId, $userId, 'campaign', $record->id));
        event(new CampaignGenerated($tenantId, $userId, $record->id, $campaign['campaign_name'] ?? ''));

        return [
            'campaign'    => $campaign,
            'content_id'  => $record->uuid,
            'provider'    => $response->provider,
            'model'       => $response->model,
            'tokens_used' => $response->totalTokens,
        ];
    }
}
