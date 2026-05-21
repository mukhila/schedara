<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AiProviderContract;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAiProvider;
use InvalidArgumentException;

class AiProviderFactory
{
    private static array $instances = [];

    public static function make(string $provider): AiProviderContract
    {
        return static::$instances[$provider] ??= match ($provider) {
            'openai' => new OpenAiProvider(),
            'claude' => new ClaudeProvider(),
            'gemini' => new GeminiProvider(),
            default  => throw new InvalidArgumentException("Unknown AI provider: {$provider}"),
        };
    }

    public static function default(): AiProviderContract
    {
        return static::make(config('ai.default_provider', 'openai'));
    }

    public static function failoverOrder(): array
    {
        return config('ai.failover.order', ['openai', 'claude', 'gemini']);
    }

    public static function configured(): array
    {
        return array_filter(
            ['openai', 'claude', 'gemini'],
            fn ($p) => static::make($p)->isConfigured()
        );
    }
}
