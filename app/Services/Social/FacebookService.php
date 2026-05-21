<?php

namespace App\Services\Social;

use App\DTOs\Social\SocialPageDTO;
use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;

class FacebookService extends BaseSocialService
{
    private const GRAPH = 'https://graph.facebook.com/v21.0';

    public function platform(): string { return 'facebook'; }

    public function getProfile(SocialAccount $account): array
    {
        return $this->get($account, self::GRAPH . '/me', [
            'fields' => 'id,name,email,picture',
        ]);
    }

    public function getPages(SocialAccount $account): array
    {
        $data = $this->get($account, self::GRAPH . '/me/accounts', [
            'fields' => 'id,name,category,picture,fan_count,access_token',
        ]);

        return collect($data['data'] ?? [])->map(fn ($p) => new SocialPageDTO(
            pageId:         $p['id'],
            pageName:       $p['name'],
            pageType:       'page',
            category:       $p['category'] ?? null,
            avatar:         $p['picture']['data']['url'] ?? null,
            accessToken:    $p['access_token'] ?? null,
            followersCount: (int) ($p['fan_count'] ?? 0),
            metadata:       $p,
        ))->values()->all();
    }

    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        $account = $config->socialAccount;
        $pageId  = $account->platform_user_id;
        $text    = $config->effectiveContent();
        $media   = $post->media()->orderBy('sort_order')->get();

        if ($media->isEmpty()) {
            return $this->post($account, self::GRAPH . "/{$pageId}/feed", ['message' => $text])['id'];
        }

        $first = $media->first();

        if ($first->isVideo()) {
            $data = $this->post($account, self::GRAPH . "/{$pageId}/videos", [
                'file_url'    => $first->publicUrl(),
                'description' => $text,
            ]);
            return (string) ($data['id'] ?? $data['video_id']);
        }

        if ($media->count() > 1) {
            $attached = $media->take(10)->map(function ($m) use ($account, $pageId) {
                $resp = $this->post($account, self::GRAPH . "/{$pageId}/photos", [
                    'url'       => $m->publicUrl(),
                    'published' => 'false',
                ]);
                return ['media_fbid' => $resp['id']];
            })->values()->all();

            return $this->post($account, self::GRAPH . "/{$pageId}/feed", [
                'message'        => $text,
                'attached_media' => json_encode($attached),
            ])['id'];
        }

        $data = $this->post($account, self::GRAPH . "/{$pageId}/photos", [
            'url'     => $first->publicUrl(),
            'message' => $text,
        ]);
        return (string) ($data['post_id'] ?? $data['id']);
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        // Facebook long-lived tokens don't refresh via standard flow;
        // user must re-authenticate. Mark as expired to trigger re-auth.
        $account->markExpired();
        return $account;
    }

    public function fetchAnalytics(SocialAccount $account): array
    {
        try {
            $response = $this->get($account, self::GRAPH . "/{$account->platform_user_id}/posts", [
                'fields' => 'id,created_time,likes.summary(true),comments.summary(true),shares,insights.metric(post_impressions,post_impressions_unique)',
                'limit'  => 50,
            ]);

            return collect($response['data'] ?? [])->map(function ($post) {
                $insightsByName = collect($post['insights']['data'] ?? [])->keyBy('name');
                $impressions    = (int) ($insightsByName['post_impressions']['values'][0]['value'] ?? 0);
                $reach          = (int) ($insightsByName['post_impressions_unique']['values'][0]['value'] ?? 0);
                $likes          = (int) ($post['likes']['summary']['total_count'] ?? 0);
                $comments       = (int) ($post['comments']['summary']['total_count'] ?? 0);
                $shares         = (int) ($post['shares']['count'] ?? 0);

                return [
                    'platform_post_id' => $post['id'],
                    'post_id'          => null,
                    'impressions'      => $impressions,
                    'reach'            => $reach,
                    'likes'            => $likes,
                    'comments'         => $comments,
                    'shares'           => $shares,
                    'saves'            => 0,
                    'video_views'      => 0,
                    'clicks'           => 0,
                    'conversions'      => 0,
                    'engagement_rate'  => $reach > 0 ? round(($likes + $comments + $shares) / $reach * 100, 4) : 0.0,
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
