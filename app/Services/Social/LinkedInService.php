<?php

namespace App\Services\Social;

use App\DTOs\Social\SocialPageDTO;
use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class LinkedInService extends BaseSocialService
{
    private const API = 'https://api.linkedin.com/v2';
    private const OIDC = 'https://www.linkedin.com/oauth/v2';

    public function platform(): string { return 'linkedin'; }

    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        $account   = $config->socialAccount;
        $text      = $config->effectiveContent();
        $media     = $post->media()->orderBy('sort_order')->get();
        $authorUrn = $this->resolveAuthorUrn($account);

        $shareContent = [
            'shareCommentary'    => ['text' => $text],
            'shareMediaCategory' => 'NONE',
        ];

        if ($media->isNotEmpty()) {
            $isVideo = $media->first()->isVideo();
            $items   = $media->take(9)
                ->map(fn ($m) => $this->uploadLinkedInAsset($account, $m->publicUrl(), $m->isVideo()))
                ->filter()
                ->values()
                ->all();

            if (! empty($items)) {
                $shareContent['shareMediaCategory'] = $isVideo ? 'VIDEO' : 'IMAGE';
                $shareContent['media']              = $items;
            }
        }

        $response = Http::withToken($account->access_token)
            ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
            ->timeout(30)
            ->asJson()
            ->post(self::API . '/ugcPosts', [
                'author'          => $authorUrn,
                'lifecycleState'  => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => $shareContent,
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                ],
            ]);

        if ($response->failed()) {
            $this->logError($account, 'publish', $response->json() ?? [], $response->status());
            $response->throw();
        }

        return $response->header('x-restli-id') ?: (string) ($response->json('id') ?? '');
    }

    private function resolveAuthorUrn(SocialAccount $account): string
    {
        $orgPage = $account->pages()->whereNotNull('page_id')->first();
        return $orgPage
            ? "urn:li:organization:{$orgPage->page_id}"
            : "urn:li:person:{$account->platform_user_id}";
    }

    private function uploadLinkedInAsset(SocialAccount $account, string $mediaUrl, bool $isVideo): ?array
    {
        $recipe = $isVideo ? 'feedvideo' : 'feedshare-image';

        $registerResp = Http::withToken($account->access_token)
            ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
            ->timeout(30)
            ->asJson()
            ->post(self::API . '/assets?action=registerUpload', [
                'registerUploadRequest' => [
                    'owner'                => "urn:li:person:{$account->platform_user_id}",
                    'recipes'              => ["urn:li:digitalmediaRecipe:{$recipe}"],
                    'serviceRelationships' => [
                        ['identifier' => 'urn:li:userGeneratedContent', 'relationshipType' => 'OWNER'],
                    ],
                ],
            ]);

        if ($registerResp->failed()) {
            return null;
        }

        $uploadUrl = $registerResp->json('value.uploadMechanism.com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest.uploadUrl');
        $assetUrn  = $registerResp->json('value.asset');

        if (! $uploadUrl || ! $assetUrn) {
            return null;
        }

        $content = $this->downloadMedia($mediaUrl);

        $uploadResp = Http::withToken($account->access_token)
            ->timeout(120)
            ->withBody($content, 'application/octet-stream')
            ->put($uploadUrl);

        if ($uploadResp->failed()) {
            return null;
        }

        return [
            'status'      => 'READY',
            'description' => ['text' => ''],
            'media'       => $assetUrn,
            'title'       => ['text' => ''],
        ];
    }

    public function getProfile(SocialAccount $account): array
    {
        return $this->get($account, self::API . '/userinfo');
    }

    public function getPages(SocialAccount $account): array
    {
        $data = $this->get($account, self::API . '/organizationAcls', [
            'q'           => 'roleAssignee',
            'projection'  => '(elements*(organization~(id,localizedName,logoV2(original~:playbackId,cropped~:playbackId)),role,state))',
        ]);

        return collect($data['elements'] ?? [])->map(function ($el) {
            $org = $el['organization~'] ?? [];
            $logo = $org['logoV2']['original~']['elements'][0]['identifiers'][0]['identifier'] ?? null;

            return new SocialPageDTO(
                pageId:         (string) ($org['id'] ?? ''),
                pageName:       $org['localizedName'] ?? 'LinkedIn Page',
                pageType:       'page',
                category:       $el['role'] ?? null,
                avatar:         $logo,
                accessToken:    null,
                followersCount: 0,
                metadata:       $el,
            );
        })->values()->all();
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if (! $account->refresh_token) {
            $account->markExpired();
            return $account;
        }

        $response = Http::asForm()->post(self::OIDC . '/accessToken', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $account->refresh_token,
            'client_id'     => config('services.linkedin-openid.client_id'),
            'client_secret' => config('services.linkedin-openid.client_secret'),
        ]);

        if ($response->failed()) {
            $account->markExpired();
            return $account;
        }

        $data = $response->json();
        $account->update([
            'access_token'     => $data['access_token'],
            'refresh_token'    => $data['refresh_token'] ?? $account->refresh_token,
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            'status'           => 'active',
        ]);

        $this->logSuccess($account, 'token_refreshed');
        return $account->fresh();
    }

    public function fetchAnalytics(SocialAccount $account): array
    {
        try {
            $orgUrn = "urn:li:organization:{$account->platform_user_id}";

            // Fetch recent UGC posts for the organisation
            $postsResp = $this->get($account, self::API . '/ugcPosts', [
                'q'          => 'authors',
                'authors'    => "List({$orgUrn})",
                'count'      => 50,
                'projection' => '(elements*(id,firstPublishedAt))',
            ]);

            $postUrns = array_map(fn ($p) => $p['id'], $postsResp['elements'] ?? []);
            if (empty($postUrns)) {
                return [];
            }

            // Fetch per-post share statistics
            $statsResp = $this->get($account, self::API . '/organizationalEntityShareStatistics', [
                'q'                    => 'organizationalEntity',
                'organizationalEntity' => $orgUrn,
                'shares'               => 'List(' . implode(',', $postUrns) . ')',
                'projection'           => '(elements*(share,totalShareStatistics))',
            ]);

            return collect($statsResp['elements'] ?? [])->map(function ($el) {
                $stats    = $el['totalShareStatistics'] ?? [];
                $impr     = (int) ($stats['impressionCount'] ?? 0);
                $clicks   = (int) ($stats['clickCount'] ?? 0);
                $likes    = (int) ($stats['likeCount'] ?? 0);
                $comments = (int) ($stats['commentCount'] ?? 0);
                $shares   = (int) ($stats['shareCount'] ?? 0);

                return [
                    'platform_post_id' => $el['share'] ?? null,
                    'post_id'          => null,
                    'impressions'      => $impr,
                    'reach'            => $impr,
                    'likes'            => $likes,
                    'comments'         => $comments,
                    'shares'           => $shares,
                    'saves'            => 0,
                    'video_views'      => 0,
                    'clicks'           => $clicks,
                    'conversions'      => 0,
                    'engagement_rate'  => $impr > 0 ? round(($likes + $comments + $shares) / $impr * 100, 4) : 0.0,
                    'ctr'              => $impr > 0 ? round($clicks / $impr * 100, 4) : 0.0,
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
            Http::asForm()->post(self::OIDC . '/revoke', [
                'token'         => $account->access_token,
                'client_id'     => config('services.linkedin-openid.client_id'),
                'client_secret' => config('services.linkedin-openid.client_secret'),
            ]);
            $this->logSuccess($account, 'revoked');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
