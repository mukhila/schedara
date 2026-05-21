<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;

class TikTokService extends BaseSocialService
{
    private const API = 'https://open.tiktokapis.com/v2';

    public function platform(): string { return 'tiktok'; }

    public function getProfile(SocialAccount $account): array
    {
        $response = $this->get($account, self::API . '/user/info/', [
            'fields' => 'open_id,union_id,avatar_url,display_name,bio_description,follower_count,following_count,likes_count,video_count',
        ]);

        return $response['data']['user'] ?? $response;
    }

    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        $account = $config->socialAccount;
        $title   = mb_substr($config->effectiveContent(), 0, 2200);
        $media   = $post->media()->orderBy('sort_order')->get();

        if ($media->isEmpty()) {
            throw new \RuntimeException('TikTok requires at least one video or image.');
        }

        $first = $media->first();

        if ($first->isVideo()) {
            return $this->publishVideo($account, $title, $first->publicUrl());
        }

        return $this->publishPhotos($account, $title, $media->map->publicUrl()->values()->all());
    }

    private function publishVideo(SocialAccount $account, string $title, string $videoUrl): string
    {
        $response = $this->http($account)
            ->asJson()
            ->post(self::API . '/post/publish/video/init/', [
                'post_info' => [
                    'title'            => $title,
                    'privacy_level'    => 'PUBLIC_TO_EVERYONE',
                    'disable_duet'     => false,
                    'disable_stitch'   => false,
                    'disable_comment'  => false,
                ],
                'source_info' => [
                    'source'    => 'PULL_FROM_URL',
                    'video_url' => $videoUrl,
                ],
            ]);

        if ($response->failed()) {
            $this->logError($account, 'publish', $response->json() ?? [], $response->status());
            $response->throw();
        }

        return (string) $response->json('data.publish_id');
    }

    private function publishPhotos(SocialAccount $account, string $title, array $photoUrls): string
    {
        $response = $this->http($account)
            ->asJson()
            ->post(self::API . '/post/publish/content/init/', [
                'post_info' => [
                    'title'           => $title,
                    'privacy_level'   => 'PUBLIC_TO_EVERYONE',
                    'disable_duet'    => false,
                    'disable_stitch'  => false,
                    'disable_comment' => false,
                ],
                'source_info' => [
                    'source'             => 'PULL_FROM_URL',
                    'photo_cover_index'  => 0,
                    'photo_images'       => $photoUrls,
                ],
                'post_mode'  => 'DIRECT_POST',
                'media_type' => 'PHOTO',
            ]);

        if ($response->failed()) {
            $this->logError($account, 'publish', $response->json() ?? [], $response->status());
            $response->throw();
        }

        return (string) $response->json('data.publish_id');
    }

    // TikTok has no pages/channels sub-accounts
    public function getPages(SocialAccount $account): array
    {
        return [];
    }

    public function fetchAnalytics(SocialAccount $account): array
    {
        try {
            $response = $this->http($account)
                ->asJson()
                ->post(self::API . '/video/list/', [
                    'fields' => ['id', 'create_time', 'like_count', 'comment_count', 'share_count', 'view_count', 'play_count'],
                ]);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('data.videos') ?? [])->map(function ($video) {
                $views    = (int) ($video['view_count'] ?? $video['play_count'] ?? 0);
                $likes    = (int) ($video['like_count'] ?? 0);
                $comments = (int) ($video['comment_count'] ?? 0);
                $shares   = (int) ($video['share_count'] ?? 0);

                return [
                    'platform_post_id' => $video['id'],
                    'post_id'          => null,
                    'impressions'      => $views,
                    'reach'            => $views,
                    'likes'            => $likes,
                    'comments'         => $comments,
                    'shares'           => $shares,
                    'saves'            => 0,
                    'video_views'      => $views,
                    'clicks'           => 0,
                    'conversions'      => 0,
                    'engagement_rate'  => $views > 0 ? round(($likes + $comments + $shares) / $views * 100, 4) : 0.0,
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
            $this->post($account, self::API . '/oauth/revoke/', [
                'token' => $account->access_token,
            ]);
            $this->logSuccess($account, 'revoked');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
