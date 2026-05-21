<?php

namespace App\Services\Post;

use App\Events\Post\PostFailed;
use App\Events\Post\PostPublished;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\PostPlatformConfig;
use App\Services\Social\SocialAuthService;
use Illuminate\Support\Facades\Log;

class PublishingService
{
    public function __construct(private readonly SocialAuthService $socialAuth) {}

    public function publishPost(Post $post): void
    {
        $post->update(['status' => 'publishing']);

        $allSucceeded = true;

        foreach ($post->platformConfigs as $config) {
            try {
                $this->publishToConfig($post, $config);
            } catch (\Throwable $e) {
                $allSucceeded = false;
                Log::error("Publishing failed for post {$post->uuid} on {$config->platform}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($allSucceeded) {
            $post->update([
                'status'       => 'published',
                'published_at' => now(),
            ]);

            if ($post->auto_repost && $post->repost_frequency) {
                $post->update([
                    'next_repost_at' => now()->addDays($post->repost_frequency),
                ]);
            }

            $post->calendarEvent?->update(['status' => 'published']);
            event(new PostPublished($post));
            PostLog::record($post, 'published', 'success', [], null, 'All platforms published successfully');
        } else {
            $pendingCount = $post->platformConfigs()->where('status', 'pending')->count();
            $newStatus    = $pendingCount > 0 ? 'partial' : 'failed';

            $post->update(['status' => $newStatus]);
            event(new PostFailed($post, 'One or more platforms failed'));
            PostLog::record($post, 'publish_failed', 'error', [], null, 'Publishing failed on one or more platforms');
        }
    }

    private function publishToConfig(Post $post, PostPlatformConfig $config): void
    {
        if (!$config->socialAccount) {
            $config->update(['status' => 'failed']);
            PostLog::record($post, 'publish_skipped', 'error', [], $config->platform, 'No social account linked');
            return;
        }

        if (!$config->socialAccount->isActive()) {
            $config->update(['status' => 'failed']);
            PostLog::record($post, 'publish_skipped', 'error', [], $config->platform, 'Social account not active');
            return;
        }

        $service          = $this->socialAuth->getService($config->platform);
        $platformPostId   = $service->publishPost($post, $config);

        $config->update([
            'status'           => 'published',
            'platform_post_id' => $platformPostId,
            'published_at'     => now(),
            'response_data'    => ['id' => $platformPostId, 'published' => true],
        ]);

        PostLog::record($post, 'platform_published', 'success', ['platform_post_id' => $platformPostId], $config->platform);
    }
}
