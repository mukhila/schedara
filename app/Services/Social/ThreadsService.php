<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class ThreadsService extends BaseSocialService
{
    private const API = 'https://graph.threads.net/v1.0';

    public function platform(): string { return 'threads'; }

    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        $account = $config->socialAccount;
        $userId  = $account->platform_user_id;
        $text    = $config->effectiveContent();
        $media   = $post->media()->orderBy('sort_order')->get();

        if ($media->count() > 1) {
            $itemIds = $media->take(20)->map(function ($m) use ($account, $userId) {
                $params = $m->isVideo()
                    ? ['media_type' => 'VIDEO', 'video_url' => $m->publicUrl(), 'is_carousel_item' => 'true']
                    : ['media_type' => 'IMAGE', 'image_url' => $m->publicUrl(), 'is_carousel_item' => 'true'];
                return $this->post($account, self::API . "/{$userId}/threads", $params)['id'];
            })->values()->all();

            $container = $this->post($account, self::API . "/{$userId}/threads", [
                'media_type' => 'CAROUSEL',
                'text'       => $text,
                'children'   => implode(',', $itemIds),
            ]);
        } elseif ($media->first()?->isVideo()) {
            $container = $this->post($account, self::API . "/{$userId}/threads", [
                'media_type' => 'VIDEO',
                'video_url'  => $media->first()->publicUrl(),
                'text'       => $text,
            ]);
        } elseif ($media->first()) {
            $container = $this->post($account, self::API . "/{$userId}/threads", [
                'media_type' => 'IMAGE',
                'image_url'  => $media->first()->publicUrl(),
                'text'       => $text,
            ]);
        } else {
            $container = $this->post($account, self::API . "/{$userId}/threads", [
                'media_type' => 'TEXT',
                'text'       => $text,
            ]);
        }

        $creationId = $container['id'];

        if ($media->isNotEmpty()) {
            $this->waitForContainer($account, $creationId);
        }

        return $this->post($account, self::API . "/{$userId}/threads_publish", [
            'creation_id' => $creationId,
        ])['id'];
    }

    private function waitForContainer(SocialAccount $account, string $creationId, int $maxAttempts = 10): void
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $status = $this->get($account, self::API . "/{$creationId}", ['fields' => 'status,error_message']);

            if (($status['status'] ?? '') === 'FINISHED') {
                return;
            }

            if (in_array($status['status'] ?? '', ['ERROR', 'EXPIRED'])) {
                throw new \RuntimeException('Threads container failed: ' . ($status['error_message'] ?? $status['status'] ?? 'unknown'));
            }

            sleep(2);
        }

        throw new \RuntimeException('Threads media container timed out after ' . ($maxAttempts * 2) . 's.');
    }

    public function getProfile(SocialAccount $account): array
    {
        return $this->get($account, self::API . '/me', [
            'fields' => 'id,name,username,threads_profile_picture_url,threads_biography',
        ]);
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        // Threads uses long-lived tokens (60 days) that can be refreshed
        $response = Http::get(self::API . '/refresh_access_token', [
            'grant_type'   => 'th_refresh_token',
            'access_token' => $account->access_token,
        ]);

        if ($response->failed()) {
            $account->markExpired();
            return $account;
        }

        $data = $response->json();
        $account->update([
            'access_token'     => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 5184000),
            'status'           => 'active',
        ]);

        $this->logSuccess($account, 'token_refreshed');
        return $account->fresh();
    }

    public function fetchAnalytics(SocialAccount $account): array
    {
        try {
            $response = $this->get($account, self::API . '/me/threads', [
                'fields' => 'id,timestamp,media_type',
                'limit'  => 30,
            ]);

            $metrics = [];
            foreach ($response['data'] ?? [] as $thread) {
                try {
                    $insights = $this->get($account, self::API . "/{$thread['id']}/insights", [
                        'metric' => 'views,likes,replies,reposts,quotes',
                    ]);

                    $byName  = collect($insights['data'] ?? [])->keyBy('name');
                    $views   = (int) ($byName['views']['values'][0]['value'] ?? 0);
                    $likes   = (int) ($byName['likes']['values'][0]['value'] ?? 0);
                    $replies = (int) ($byName['replies']['values'][0]['value'] ?? 0);
                    $reposts = (int) ($byName['reposts']['values'][0]['value'] ?? 0);
                    $quotes  = (int) ($byName['quotes']['values'][0]['value'] ?? 0);

                    $metrics[] = [
                        'platform_post_id' => $thread['id'],
                        'post_id'          => null,
                        'impressions'      => $views,
                        'reach'            => $views,
                        'likes'            => $likes,
                        'comments'         => $replies,
                        'shares'           => $reposts + $quotes,
                        'saves'            => 0,
                        'video_views'      => 0,
                        'clicks'           => 0,
                        'conversions'      => 0,
                        'engagement_rate'  => $views > 0 ? round(($likes + $replies + $reposts + $quotes) / $views * 100, 4) : 0.0,
                        'ctr'              => 0.0,
                        'spend'            => 0.0,
                        'revenue'          => 0.0,
                    ];
                } catch (\Throwable) {
                    // skip this thread if insights unavailable
                }
            }

            return $metrics;
        } catch (\Throwable) {
            return [];
        }
    }

    public function revokeToken(SocialAccount $account): bool
    {
        // Threads doesn't have a formal revocation endpoint yet
        return true;
    }
}
