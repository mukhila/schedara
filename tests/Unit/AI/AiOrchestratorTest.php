<?php

namespace Tests\Unit\AI;

use App\DTOs\AI\AiRequestDTO;
use App\DTOs\AI\AiResponseDTO;
use App\Exceptions\AI\UsageLimitExceededException;
use App\Services\AI\AiOrchestrator;
use App\Services\AI\UsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    private function makeResponse(string $content = '{"ok":true}', string $provider = 'openai'): AiResponseDTO
    {
        return new AiResponseDTO(
            content:         $content,
            inputTokens:     40,
            outputTokens:    80,
            totalTokens:     120,
            costEstimate:    0.0012,
            provider:        $provider,
            model:           'gpt-4o',
            processingTimeMs:350,
        );
    }

    public function test_orchestrator_throws_when_usage_limit_exceeded(): void
    {
        $mockUsage = Mockery::mock(UsageService::class);
        $mockUsage->shouldReceive('isOverLimit')->with(1)->andReturn(true);

        $orchestrator = new AiOrchestrator($mockUsage);

        $this->expectException(UsageLimitExceededException::class);

        $orchestrator->generate(
            new AiRequestDTO(prompt: 'test prompt', model: 'gpt-4o', maxTokens: 100),
            requestType: 'test',
            tenantId:    1,
            userId:      1,
        );
    }

    public function test_response_dto_decodes_valid_json(): void
    {
        $dto = $this->makeResponse('{"key":"value","num":42}');

        $decoded = $dto->decoded();
        $this->assertEquals('value', $decoded['key']);
        $this->assertEquals(42, $decoded['num']);
    }

    public function test_response_dto_decoded_returns_empty_array_for_invalid_json(): void
    {
        $dto = $this->makeResponse('not valid json');

        $this->assertSame([], $dto->decoded());
    }

    public function test_request_dto_with_model_returns_new_instance(): void
    {
        $original = new AiRequestDTO(
            prompt:    'Generate a caption',
            model:     'gpt-4o',
            maxTokens: 100,
        );

        $modified = $original->withModel('gpt-4o-mini');

        $this->assertNotSame($original, $modified);
        $this->assertEquals('gpt-4o-mini', $modified->model);
        $this->assertEquals('gpt-4o', $original->model);
    }

    public function test_request_dto_carries_system_prompt(): void
    {
        $dto = new AiRequestDTO(
            prompt:       'Write something',
            model:        'gpt-4o',
            systemPrompt: 'You are a helpful assistant.',
            maxTokens:    200,
        );

        $this->assertEquals('You are a helpful assistant.', $dto->systemPrompt);
    }

    public function test_response_dto_total_tokens(): void
    {
        $dto = $this->makeResponse();

        $this->assertEquals(120, $dto->totalTokens);
        $this->assertEquals(40,  $dto->inputTokens);
        $this->assertEquals(80,  $dto->outputTokens);
    }
}
