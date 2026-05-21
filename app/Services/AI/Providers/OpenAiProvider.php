<?php

namespace App\Services\AI\Providers;

use App\DTOs\AI\AiRequestDTO;
use App\DTOs\AI\AiResponseDTO;
use App\Services\AI\Contracts\AiProviderContract;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProviderContract
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai.providers.openai', []);
    }

    public function complete(AiRequestDTO $request): AiResponseDTO
    {
        $start    = hrtime(true);
        $messages = $this->buildMessages($request);

        $payload = [
            'model'       => $request->model,
            'messages'    => $messages,
            'temperature' => $request->temperature,
            'max_tokens'  => $request->maxTokens,
        ];

        if ($request->jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken($this->config['api_key'])
            ->baseUrl($this->config['base_url'])
            ->timeout($this->config['timeout'] ?? 90)
            ->post('/chat/completions', $payload)
            ->throw();

        $body     = $response->json();
        $ms       = (int) ((hrtime(true) - $start) / 1_000_000);
        $content  = $body['choices'][0]['message']['content'] ?? '';
        $usage    = $body['usage'] ?? [];
        $inTok    = $usage['prompt_tokens']     ?? 0;
        $outTok   = $usage['completion_tokens'] ?? 0;
        $cost     = $this->estimateCost($inTok, $outTok, $request->model);

        return new AiResponseDTO($content, $inTok, $outTok, $inTok + $outTok, $cost, 'openai', $request->model, $ms);
    }

    public function getProviderName(): string    { return 'openai'; }
    public function getDefaultModel(): string    { return $this->config['default_model'] ?? 'gpt-4o'; }
    public function getSupportedModels(): array  { return $this->config['models'] ?? []; }
    public function isConfigured(): bool         { return !empty($this->config['api_key']); }

    public function estimateCost(int $inputTokens, int $outputTokens, string $model): float
    {
        $pricing  = $this->config['pricing'][$model] ?? ['input' => 0.005, 'output' => 0.015];
        return (($inputTokens / 1000) * $pricing['input']) + (($outputTokens / 1000) * $pricing['output']);
    }

    private function buildMessages(AiRequestDTO $request): array
    {
        $messages = [];

        if ($request->systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $request->systemPrompt];
        }

        // Inject conversation history for chat mode
        foreach ($request->history as $msg) {
            $messages[] = $msg;
        }

        $messages[] = ['role' => 'user', 'content' => $request->prompt];

        return $messages;
    }
}
