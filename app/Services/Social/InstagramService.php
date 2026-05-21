<?php

namespace App\Services\Social;

use App\DTOs\Social\SocialPageDTO;
use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;

class InstagramService extends BaseSocialService
{
    private const GRAPH = 'https://graph.facebook.com/v21.0';

    public function platform(): string { return 'instagram'; }

    public function getProfile(SocialAccount $account): array
    {
        // Instagram Business accounts are linked via Facebook Graph API
        return $this->get($account, self::GRAPH . '/me', [
            'fields' => 'id,name,email,picture',
        ]);
    }

    public function getPages(SocialAccount $account): array
    {
        // Get Facebook pages first, then find their linked Instagram business accounts
        $fbPages = $this->get($account, self::GRAPH . '/me/accounts', [
            'fields' => 'id,name,instagram_business_account{id,name,username,profile_picture_url,followers_count}',
        ]);

        $pages = [];
        foreach ($fbPages['data'] ?? [] as $fbPage) {
            $ig = $fbPage['instagram_business_account'] ?? null;
            if (! $ig) continue;

            $pages[] = new SocialPageDTO(
                pageId:         $ig['id'],
                pageName:       $ig['name'] ?? $ig['username'] ?? 'Instagram Account',
                pageType:       'profile',
                category:       'Instagram Business',
                avatar:         $ig['profile_picture_url'] ?? null,
                accessToken:    null, // uses parent account token
                followersCount: (int) ($ig['followers_count'] ?? 0),
                metadata:       array_merge($ig, ['fb_page_id' => $fbPage['id']]),
            );
        }

        return $pages;
    }

    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        $account  = $config->socialAccount;
        $igUserId = $account->platform_user_id;
        $text     = $config->effectiveContent();
        $media    = $post->media()->orderBy('sort_order')->get();

        if ($media->count() > 1) {
            $itemIds = $media->take(10)->map(function ($m) use ($account, $igUserId) {
                $params = $m->isVideo()
                    ? ['media_type' => 'VIDEO', 'video_url' => $m->publicUrl(), 'is_carousel_item' => 'true']
                    : ['media_type' => 'IMAGE', 'image_url' => $m->publicUrl(), 'is_carousel_item' => 'true'];
                return $this->post($account, self::GRAPH . "/{$igUserId}/media", $params)['id'];
            })->values()->all();

            $container = $this->post($account, self::GRAPH . "/{$igUserId}/media", [
                'media_type' => 'CAROUSEL',
                'caption'    => $text,
                'children'   => implode(',', $itemIds),
            ]);
        } elseif ($media->first()?->isVideo()) {
            $container = $this->post($account, self::GRAPH . "/{$igUserId}/media", [
                'media_type' => 'REELS',
                'video_url'  => $media->first()->publicUrl(),
                'caption'    => $text,
            ]);
        } elseif ($media->first()) {
            $container = $this->post($account, self::GRAPH . "/{$igUserId}/media", [
                'media_type' => 'IMAGE',
                'image_url'  => $media->first()->publicUrl(),
                'caption'    => $text,
            ]);
        } else {
            throw new \RuntimeException('Instagram requires at least one image or video.');
        }

        $this->waitForContainer($account, $container['id']);

        return $this->post($account, self::GRAPH . "/{$igUserId}/media_publish", [
            'creation_id' => $container['id'],
        ])['id'];
    }

    private function waitForContainer(SocialAccount $account, string $creationId, int $maxAttempts = 15): void
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $status = $this->get($account, self::GRAPH . "/{$creationId}", ['fields' => 'status_code']);

            if (($status['status_code'] ?? '') === 'FINISHED') {
                return;
            }

            if (($status['status_code'] ?? '') === 'ERROR') {
                throw new \RuntimeException('Instagram media container error: ' . ($status['status'] ?? 'unknown'));
            }

            sleep(2);
        }

        throw new \RuntimeException('Instagram media container timed out after ' . ($maxAttempts * 2) . 's.');
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        $account->markExpired();
        return $account;
    }

    public function fetchAnalytics(SocialAccount $account): array
    {
        try {
            $response = $this->get($account, self::GRAPH . "/{$account->platform_user_id}/media", [
                'fields' => 'id,timestamp,like_count,comments_count,media_type,insights.metric(impressions,reach,saved,video_views)',
                'limit'  => 50,
            ]);

            return collect($response['data'] ?? [])->map(function ($media) {
                $insightsByName = collect($media['insights']['data'] ?? [])->keyBy('name');
                $impressions    = (int) ($insightsByName['impressions']['values'][0]['value'] ?? 0);
                $reach          = (int) ($insightsByName['reach']['values'][0]['value'] ?? 0);
                $saves          = (int) ($insightsByName['saved']['values'][0]['value'] ?? 0);
                $videoViews     = (int) ($insightsByName['video_views']['values'][0]['value'] ?? 0);
                $likes          = (int) ($media['like_count'] ?? 0);
                $comments       = (int) ($media['comments_count'] ?? 0);

                return [
                    'platform_post_id' => $media['id'],
                    'post_id'          => null,
                    'impressions'      => $impressions,
                    'reach'            => $reach,
                    'likes'            => $likes,
                    'comments'         => $comments,
                    'shares'           => 0,
                    'saves'            => $saves,
                    'video_views'      => $videoViews,
                    'clicks'           => 0,
                    'conversions'      => 0,
                    'engagement_rate'  => $reach > 0 ? round(($likes + $comments + $saves) / $reach * 100, 4) : 0.0,
                    'ctr'              => 0.0,
                    'spend'            => 0.0,
                    'revenue'          => 0.0,
                ];
            })->values()->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public function revokeToken(SocialAccount $account): bool
    {
        try {
            $this->post($account, self::GRAPH . "/{$account->platform_user_id}/permissions");
            $this->logSuccess($account, 'revoked');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
