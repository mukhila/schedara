<?php

namespace App\Services\AI\Providers;

use App\DTOs\AI\AiRequestDTO;
use App\DTOs\AI\AiResponseDTO;
use App\Services\AI\Contracts\AiProviderContract;
use Illuminate\Support\Facades\Http;

class ClaudeProvider implements AiProviderContract
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai.providers.claude', []);
    }

    public function complete(AiRequestDTO $request): AiResponseDTO
    {
        $start    = hrtime(true);
        $messages = $this->buildMessages($request);

        $payload = [
            'model'      => $request->model,
            'max_tokens' => $request->maxTokens,
            'messages'   => $messages,
        ];

        if ($request->systemPrompt) {
            $payload['system'] = $request->systemPrompt;
        }

        if ($request->temperature !== 1.0) {
            $payload['temperature'] = $request->temperature;
        }

        $response = Http::withHeaders([
                'x-api-key'        => $this->config['api_key'],
                'anthropic-version'=> $this->config['api_version'] ?? '2023-06-01',
                'content-type'     => 'application/json',
            ])
            ->baseUrl($this->config['base_url'])
            ->timeout($this->config['timeout'] ?? 90)
            ->post('/messages', $payload)
            ->throw();

        $body   = $response->json();
        $ms     = (int) ((hrtime(true) - $start) / 1_000_000);

        // Extract text from Claude's content blocks
        $content = '';
        foreach ($body['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $content .= $block['text'];
            }
        }

        $usage  = $body['usage'] ?? [];
        $inTok  = $usage['input_tokens']  ?? 0;
        $outTok = $usage['output_tokens'] ?? 0;
        $cost   = $this->estimateCost($inTok, $outTok, $request->model);

        return new AiResponseDTO($content, $inTok, $outTok, $inTok + $outTok, $cost, 'claude', $request->model, $ms);
    }

    public function getProviderName(): string   { return 'claude'; }
    public function getDefaultModel(): string   { return $this->config['default_model'] ?? 'claude-3-5-sonnet-20241022'; }
    public function getSupportedModels(): array { return $this->config['models'] ?? []; }
    public function isConfigured(): bool        { return !empty($this->config['api_key']); }

    public function estimateCost(int $inputTokens, int $outputTokens, string $model): float
    {
        $pricing = $this->config['pricing'][$model] ?? ['input' => 0.003, 'output' => 0.015];
        return (($inputTokens / 1000) * $pricing['input']) + (($outputTokens / 1000) * $pricing['output']);
    }

    private function buildMessages(AiRequestDTO $request): array
    {
        $messages = [];

        foreach ($request->history as $msg) {
            $messages[] = $msg;
        }

        $messages[] = ['role' => 'user', 'content' => $request->prompt];

        return $messages;
    }
}
