<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Events\AI\AiContentGenerated;
use App\Models\AiGeneratedContent;

class CaptionService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function generate(
        array   $inputs,
        int     $tenantId,
        int     $userId,
        ?string $provider = null,
        ?string $model    = null,
    ): array {
        $brandVoice = $inputs['brand_voice_id']
            ? \App\Models\AiBrandVoice::where('uuid', $inputs['brand_voice_id'])->first()
            : null;

        $request = new AiRequestDTO(
            prompt:      $this->prompts->caption($inputs),
            model:       '',
            systemPrompt:$this->prompts->system('You are an expert social media copywriter.', $brandVoice),
            temperature: 0.8,
            maxTokens:   2000,
            jsonMode:    true,
        );

        $response = $this->orchestrator->generate($request, 'caption', $tenantId, $userId, $provider, $model);
        $decoded  = $response->decoded();
        $captions = $decoded['captions'] ?? [];

        // Persist generated content
        $record = AiGeneratedContent::create([
            'tenant_id'         => $tenantId,
            'user_id'           => $userId,
            'content_type'      => 'caption',
            'platform'          => $inputs['platform'] ?? null,
            'title'             => 'Caption: ' . ($inputs['topic'] ?? ''),
            'generated_content' => $captions[0]['long'] ?? ($response->content),
            'variations'        => $captions,
            'metadata'          => [
                'tone'     => $inputs['tone'] ?? '',
                'topic'    => $inputs['topic'] ?? '',
                'provider' => $response->provider,
                'model'    => $response->model,
                'tokens'   => $response->totalTokens,
            ],
        ]);

        event(new AiContentGenerated($tenantId, $userId, 'caption', $record->id));

        return [
            'captions'    => $captions,
            'content_id'  => $record->uuid,
            'provider'    => $response->provider,
            'model'       => $response->model,
            'tokens_used' => $response->totalTokens,
            'cost'        => $response->costEstimate,
        ];
    }
}
