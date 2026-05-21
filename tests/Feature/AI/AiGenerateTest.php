<?php

namespace Tests\Feature\AI;

use App\Models\AiBrandVoice;
use App\Models\AiUsageLimit;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AI\AiOrchestrator;
use App\DTOs\AI\AiResponseDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiGenerateTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create();

        $this->user->tenants()->attach($this->tenant->id, [
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum');
        $this->withHeader('X-Tenant-ID', $this->tenant->uuid);
    }

    private function mockOrchestrator(array $data): void
    {
        $this->mock(AiOrchestrator::class, function ($mock) use ($data) {
            $mock->shouldReceive('generate')->andReturn(new AiResponseDTO(
                content:         json_encode($data),
                inputTokens:     40,
                outputTokens:    80,
                totalTokens:     120,
                costEstimate:    0.0012,
                provider:        'openai',
                model:           'gpt-4o',
                processingTimeMs:350,
            ));
        });
    }

    public function test_caption_endpoint_returns_structured_response(): void
    {
        $this->mockOrchestrator([
            'captions' => [
                ['short' => 'Test caption', 'long' => 'Longer test caption', 'emoji_version' => 'Test 🎉', 'cta' => 'Shop now', 'tone_used' => 'professional'],
            ],
        ]);

        $response = $this->postJson('/api/ai/assistant/caption', [
            'topic'    => 'New shoes',
            'platform' => 'instagram',
            'tone'     => 'professional',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['captions', 'provider', 'model']]);
    }

    public function test_hashtag_endpoint_returns_hashtag_array(): void
    {
        $this->mockOrchestrator([
            'hashtags' => [
                ['tag' => '#running', 'category' => 'niche', 'estimated_reach' => 'high', 'competition' => 'medium'],
            ],
        ]);

        $response = $this->postJson('/api/ai/assistant/hashtags', [
            'topic'    => 'running shoes',
            'platform' => 'instagram',
            'count'    => 15,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['hashtags']]);
    }

    public function test_content_ideas_endpoint_returns_ideas(): void
    {
        $this->mockOrchestrator([
            'ideas' => [
                ['title' => 'Test idea', 'description' => 'Desc', 'format' => 'reel', 'estimated_engagement' => 'high', 'hook' => 'Hook', 'cta' => 'CTA'],
            ],
        ]);

        $response = $this->postJson('/api/ai/assistant/content-ideas', [
            'industry' => 'fashion',
            'platform' => 'instagram',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['ideas']]);
    }

    public function test_seo_optimize_endpoint_returns_scores(): void
    {
        $this->mockOrchestrator([
            'seo_score'          => 82,
            'readability_score'  => 74,
            'optimized_content'  => 'Optimized text',
            'meta_description'   => 'Meta here',
            'recommendations'    => ['Add keywords', 'Shorten sentences'],
        ]);

        $response = $this->postJson('/api/ai/assistant/seo-optimize', [
            'content'  => 'My post content here',
            'platform' => 'blog',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['seo_score', 'readability_score', 'optimized_content', 'meta_description', 'recommendations']]);
    }

    public function test_ad_copy_endpoint_returns_variations(): void
    {
        $this->mockOrchestrator([
            'variations' => [
                ['headline' => 'Best shoes ever', 'primary_text' => 'Buy now', 'cta' => 'Shop Now'],
            ],
        ]);

        $response = $this->postJson('/api/ai/assistant/ad-copy', [
            'product'           => 'Running shoes',
            'value_proposition' => 'Lightweight, durable',
            'platform'          => 'facebook',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['variations']]);
    }

    public function test_response_suggestions_endpoint_returns_suggestions(): void
    {
        $this->mockOrchestrator([
            'responses' => [
                ['text' => 'Thank you for your comment!', 'tone' => 'friendly', 'length' => 'short'],
            ],
        ]);

        $response = $this->postJson('/api/ai/assistant/response-suggestions', [
            'comment' => 'Great product!',
            'context' => 'comment',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['responses']]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->withoutMiddleware(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);

        $this->postJson('/api/ai/assistant/caption', ['topic' => 'test'])
            ->assertUnauthorized();
    }

    public function test_usage_limit_exceeded_returns_error(): void
    {
        AiUsageLimit::create([
            'tenant_id'           => $this->tenant->id,
            'current_month_usage' => 100001,
            'monthly_limit'       => 100000,
            'reset_date'          => now()->startOfMonth()->addMonth()->toDateString(),
        ]);

        // Don't mock orchestrator — the real one should check the limit
        $response = $this->postJson('/api/ai/assistant/caption', [
            'topic'    => 'Test',
            'platform' => 'instagram',
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure(['error']);
    }
}
