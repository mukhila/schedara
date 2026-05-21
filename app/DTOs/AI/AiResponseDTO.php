<?php

namespace App\DTOs\AI;

readonly class AiResponseDTO
{
    public function __construct(
        public string $content,
        public int    $inputTokens,
        public int    $outputTokens,
        public int    $totalTokens,
        public float  $costEstimate,
        public string $provider,
        public string $model,
        public int    $processingTimeMs,
    ) {}

    public function toArray(): array
    {
        return [
            'content'           => $this->content,
            'input_tokens'      => $this->inputTokens,
            'output_tokens'     => $this->outputTokens,
            'total_tokens'      => $this->totalTokens,
            'cost_estimate'     => $this->costEstimate,
            'provider'          => $this->provider,
            'model'             => $this->model,
            'processing_time_ms'=> $this->processingTimeMs,
        ];
    }

    public function decoded(): array
    {
        $decoded = json_decode($this->content, true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }
}
