<?php

namespace App\DTOs\AI;

readonly class AiRequestDTO
{
    public function __construct(
        public string  $prompt,
        public string  $model,
        public ?string $systemPrompt  = null,
        public float   $temperature   = 0.7,
        public int     $maxTokens     = 1500,
        public bool    $jsonMode      = false,
        public array   $history       = [],   // [['role'=>'user','content'=>'...']]
        public array   $extra         = [],
    ) {}

    public function withModel(string $model): self
    {
        return new self(
            $this->prompt, $model, $this->systemPrompt,
            $this->temperature, $this->maxTokens, $this->jsonMode,
            $this->history, $this->extra,
        );
    }
}
