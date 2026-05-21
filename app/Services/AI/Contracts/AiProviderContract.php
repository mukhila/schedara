<?php

namespace App\Services\AI\Contracts;

use App\DTOs\AI\AiRequestDTO;
use App\DTOs\AI\AiResponseDTO;

interface AiProviderContract
{
    public function complete(AiRequestDTO $request): AiResponseDTO;

    public function getProviderName(): string;

    public function getDefaultModel(): string;

    public function getSupportedModels(): array;

    public function estimateCost(int $inputTokens, int $outputTokens, string $model): float;

    public function isConfigured(): bool;
}
