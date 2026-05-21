<?php

namespace App\Services\Social;

use App\DTOs\Social\SocialPageDTO;
use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class PinterestService extends BaseSocialService
{
    private const API = 'https://api.pinterest.com/v5';
    private const TOKEN_URL = 'https://api.pinterest.com/v5/oauth/token';

    public function platform(): string { return 'pinterest'; }

    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        $account = $config->socialAccount;
        $boardId = $config->media_override['board_id'] ?? $this->resolveBoardId($account);

        if (! $boardId) {
            throw new \RuntimeException('Pinterest: no board configured. Link a board to this account.');
        }

        $media = $post->media()->orderBy('sort_order')->first();

        if (! $media) {
            throw new \RuntimeException('Pinterest requires at least one image or video.');
        }

        $pinBody = [
            'board_id' => $boardId,
            'note'     => mb_substr($config->effectiveContent(), 0, 500),
            'title'    => mb_substr($post->title ?? $config->effectiveContent(), 0, 100),
        ];

        if ($media->isVideo()) {
            $pinBody['media_source'] = ['source_type' => 'video_url', 'url' => $media->publicUrl()];
        } else {
            $pinBody['media_source'] = ['source_type' => 'image_url', 'url' => $media->publicUrl()];
        }

        $response = $this->http($account)->asJson()->post(self::API . '/pins', $pinBody);

        if ($response->failed()) {
            $this->logError($account, 'publish', $response->json() ?? [], $response->status());
            $response->throw();
        }

        return (string) $response->json('id');
    }

    private function resolveBoardId(SocialAccount $account): ?string
    {
        return $account->pages()->whereNotNull('page_id')->first()?->page_id;
    }

    public function getProfile(SocialAccount $account): array
    {
        return $this->get($account, self::API . '/user_account');
    }

    public function getPages(SocialAccount $account): array
    {
        $data = $this->get($account, self::API . '/boards', [
            'page_size' => 100,
        ]);

        return collect($data['items'] ?? [])->map(fn ($b) => new SocialPageDTO(
            pageId:         $b['id'],
            pageName:       $b['name'],
            pageType:       'board',
            category:       'Board',
            avatar:         $b['media']['image_cover_url'] ?? null,
            accessToken:    null,
            followersCount: (int) ($b['follower_count'] ?? 0),
            metadata:       $b,
        ))->values()->all();
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if (! $account->refresh_token) {
            $account->markExpired();
            return $account;
        }

        $response = Http::withBasicAuth(
            config('services.pinterest.client_id'),
            config('services.pinterest.client_secret')
        )->asForm()->post(self::TOKEN_URL, [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $account->refresh_token,
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
            $response = $this->get($account, self::API . '/user_account/analytics', [
                'start_date'   => now()->subDays(30)->toDateString(),
                'end_date'     => now()->toDateString(),
                'metric_types' => 'IMPRESSION,ENGAGEMENT,OUTBOUND_CLICK,PIN_CLICK,SAVE',
            ]);

            return collect($response['all']['daily_metrics'] ?? [])->map(function ($day) {
                $impr       = (int) ($day['metrics']['IMPRESSION'] ?? 0);
                $engagement = (int) ($day['metrics']['ENGAGEMENT'] ?? 0);
                $clicks     = (int) ($day['metrics']['OUTBOUND_CLICK'] ?? $day['metrics']['PIN_CLICK'] ?? 0);
                $saves      = (int) ($day['metrics']['SAVE'] ?? 0);

                return [
                    'platform_post_id' => 'daily-' . ($day['date'] ?? uniqid()),
                    'post_id'          => null,
                    'impressions'      => $impr,
                    'reach'            => $impr,
                    'likes'            => 0,
                    'comments'         => 0,
                    'shares'           => 0,
                    'saves'            => $saves,
                    'video_views'      => 0,
                    'clicks'           => $clicks,
                    'conversions'      => 0,
                    'engagement_rate'  => $impr > 0 ? round($engagement / $impr * 100, 4) : 0.0,
                    'ctr'              => $impr > 0 ? round($clicks / $impr * 100, 4) : 0.0,
                    'spend'            => 0.0,
                    'revenue'          => 0.0,
                ];
            })->values()->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
