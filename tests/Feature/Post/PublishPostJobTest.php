<?php

namespace Tests\Feature\Post;

use App\Jobs\Post\PublishPostJob;
use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;
use App\Models\SocialPlatform;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Social\FacebookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PublishPostJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_post_job_is_dispatched_when_post_is_scheduled(): void
    {
        Queue::fake();

        $tenant = Tenant::factory()->create();
        $user   = User::factory()->create();
        $post   = Post::factory()->create([
            'tenant_id'    => $tenant->id,
            'status'       => 'scheduled',
            'scheduled_at' => now()->subMinute(),
            'created_by'   => $user->id,
        ]);

        Queue::assertNothingPushed();

        PublishPostJob::dispatch($post);

        Queue::assertPushed(PublishPostJob::class, function ($job) use ($post) {
            return true; // job was dispatched
        });
    }

    public function test_publish_post_job_marks_post_published_on_success(): void
    {
        $tenant   = Tenant::factory()->create();
        $user     = User::factory()->create();
        $platform = SocialPlatform::firstOrCreate(['slug' => 'facebook'], ['name' => 'Facebook']);

        $account = SocialAccount::factory()->create([
            'tenant_id'   => $tenant->id,
            'platform_id' => $platform->id,
            'status'      => 'active',
        ]);

        $post = Post::factory()->create([
            'tenant_id'    => $tenant->id,
            'status'       => 'scheduled',
            'scheduled_at' => now()->subMinute(),
            'created_by'   => $user->id,
        ]);

        $config = PostPlatformConfig::factory()->create([
            'post_id'           => $post->id,
            'social_account_id' => $account->id,
            'status'            => 'pending',
        ]);

        // Mock the Facebook service to return a fake post ID
        $this->mock(FacebookService::class, function ($mock) {
            $mock->shouldReceive('publishPost')->andReturn('fake_post_123');
        });

        (new PublishPostJob($post))->handle();

        $this->assertEquals('published', $post->fresh()->status);
    }

    public function test_publish_post_job_marks_post_failed_when_service_throws(): void
    {
        $tenant   = Tenant::factory()->create();
        $user     = User::factory()->create();
        $platform = SocialPlatform::firstOrCreate(['slug' => 'facebook'], ['name' => 'Facebook']);

        $account = SocialAccount::factory()->create([
            'tenant_id'   => $tenant->id,
            'platform_id' => $platform->id,
            'status'      => 'active',
        ]);

        $post = Post::factory()->create([
            'tenant_id'    => $tenant->id,
            'status'       => 'scheduled',
            'scheduled_at' => now()->subMinute(),
            'created_by'   => $user->id,
        ]);

        PostPlatformConfig::factory()->create([
            'post_id'           => $post->id,
            'social_account_id' => $account->id,
            'status'            => 'pending',
        ]);

        $this->mock(FacebookService::class, function ($mock) {
            $mock->shouldReceive('publishPost')
                ->andThrow(new \RuntimeException('API error'));
        });

        try {
            (new PublishPostJob($post))->handle();
        } catch (\Throwable) {
            // expected — job throws after marking failed
        }

        $this->assertEquals('failed', $post->fresh()->status);
    }

    public function test_already_published_post_is_skipped(): void
    {
        $tenant = Tenant::factory()->create();
        $user   = User::factory()->create();
        $post   = Post::factory()->create([
            'tenant_id'  => $tenant->id,
            'status'     => 'published',
            'created_by' => $user->id,
        ]);

        // Should not throw; idempotent
        (new PublishPostJob($post))->handle();

        $this->assertEquals('published', $post->fresh()->status);
    }
}
