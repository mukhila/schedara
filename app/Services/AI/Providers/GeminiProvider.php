<?php

namespace App\Services\AI\Providers;

use App\DTOs\AI\AiRequestDTO;
use App\DTOs\AI\AiResponseDTO;
use App\Services\AI\Contracts\AiProviderContract;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AiProviderContract
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai.providers.gemini', []);
    }

    public function complete(AiRequestDTO $request): AiResponseDTO
    {
        $start   = hrtime(true);
        $model   = $request->model;
        $apiKey  = $this->config['api_key'];
        $baseUrl = rtrim($this->config['base_url'], '/');

        $contents = $this->buildContents($request);
        $payload  = ['contents' => $contents];

        if ($request->systemPrompt) {
            $payload['systemInstruction'] = ['parts' => [['text' => $request->systemPrompt]]];
        }

        $payload['generationConfig'] = [
            'temperature'     => $request->temperature,
            'maxOutputTokens' => $request->maxTokens,
        ];

        if ($request->jsonMode) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $url = "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout($this->config['timeout'] ?? 90)
            ->post($url, $payload)
            ->throw();

        $body    = $response->json();
        $ms      = (int) ((hrtime(true) - $start) / 1_000_000);
        $content = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $usage  = $body['usageMetadata'] ?? [];
        $inTok  = $usage['promptTokenCount']     ?? 0;
        $outTok = $usage['candidatesTokenCount'] ?? 0;
        $cost   = $this->estimateCost($inTok, $outTok, $model);

        return new AiResponseDTO($content, $inTok, $outTok, $inTok + $outTok, $cost, 'gemini', $model, $ms);
    }

    public function getProviderName(): string   { return 'gemini'; }
    public function getDefaultModel(): string   { return $this->config['default_model'] ?? 'gemini-1.5-pro'; }
    public function getSupportedModels(): array { return $this->config['models'] ?? []; }
    public function isConfigured(): bool        { return !empty($this->config['api_key']); }

    public function estimateCost(int $inputTokens, int $outputTokens, string $model): float
    {
        $pricing = $this->config['pricing'][$model] ?? ['input' => 0.00125, 'output' => 0.005];
        return (($inputTokens / 1000) * $pricing['input']) + (($outputTokens / 1000) * $pricing['output']);
    }

    private function buildContents(AiRequestDTO $request): array
    {
        $contents = [];

        // Inject conversation history
        foreach ($request->history as $msg) {
            $role       = $msg['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = ['role' => $role, 'parts' => [['text' => $msg['content']]]];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $request->prompt]]];

        return $contents;
    }
}
