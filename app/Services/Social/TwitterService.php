<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class TwitterService extends BaseSocialService
{
    private const API = 'https://api.twitter.com/2';
    private const TOKEN_URL = 'https://api.twitter.com/2/oauth2/token';

    public function platform(): string { return 'twitter'; }

    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        $account = $config->socialAccount;
        $text    = mb_substr($config->effectiveContent(), 0, 280);
        $media   = $post->media()->orderBy('sort_order')->get();
        $payload = ['text' => $text];

        if ($media->isNotEmpty()) {
            $mediaIds = $media->take(4)
                ->map(fn ($m) => $this->uploadTwitterMedia($account, $m->publicUrl(), $m->mime_type ?? 'image/jpeg'))
                ->filter()
                ->values()
                ->all();

            if (! empty($mediaIds)) {
                $payload['media'] = ['media_ids' => $mediaIds];
            }
        }

        $response = $this->http($account)->asJson()->post(self::API . '/tweets', $payload);

        if ($response->failed()) {
            $this->logError($account, 'publish', $response->json() ?? [], $response->status());
            $response->throw();
        }

        return (string) $response->json('data.id');
    }

    private function uploadTwitterMedia(SocialAccount $account, string $mediaUrl, string $mimeType): ?string
    {
        $content = $this->downloadMedia($mediaUrl);

        $response = Http::withToken($account->access_token)
            ->timeout(60)
            ->attach('media', $content, 'media')
            ->post('https://upload.twitter.com/1.1/media/upload.json');

        return $response->successful() ? (string) $response->json('media_id_string') : null;
    }

    public function getProfile(SocialAccount $account): array
    {
        return $this->get($account, self::API . '/users/me', [
            'user.fields' => 'id,name,username,description,profile_image_url,public_metrics,verified',
        ]);
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if (! $account->refresh_token) {
            $account->markExpired();
            return $account;
        }

        $credentials = base64_encode(
            config('services.twitter-oauth-2.client_id') . ':' . config('services.twitter-oauth-2.client_secret')
        );

        $response = Http::withHeaders(['Authorization' => "Basic {$credentials}"])
            ->asForm()
            ->post(self::TOKEN_URL, [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $account->refresh_token,
                'client_id'     => config('services.twitter-oauth-2.client_id'),
            ]);

        if ($response->failed()) {
            $account->markExpired();
            return $account;
        }

        $data = $response->json();
        $account->update([
            'access_token'     => $data['access_token'],
            'refresh_token'    => $data['refresh_token'] ?? $account->refresh_token,
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 7200),
            'status'           => 'active',
        ]);

        $this->logSuccess($account, 'token_refreshed');
        return $account->fresh();
    }

    public function fetchAnalytics(SocialAccount $account): array
    {
        try {
            $response = $this->get($account, self::API . "/users/{$account->platform_user_id}/tweets", [
                'tweet.fields' => 'public_metrics,created_at',
                'max_results'  => 100,
            ]);

            return collect($response['data'] ?? [])->map(function ($tweet) {
                $pub     = $tweet['public_metrics'] ?? [];
                $likes   = (int) ($pub['like_count'] ?? 0);
                $reposts = (int) ($pub['retweet_count'] ?? 0);
                $replies = (int) ($pub['reply_count'] ?? 0);
                $quotes  = (int) ($pub['quote_count'] ?? 0);
                $impr    = (int) ($pub['impression_count'] ?? 0);

                return [
                    'platform_post_id' => $tweet['id'],
                    'post_id'          => null,
                    'impressions'      => $impr,
                    'reach'            => $impr,
                    'likes'            => $likes,
                    'comments'         => $replies,
                    'shares'           => $reposts + $quotes,
                    'saves'            => (int) ($pub['bookmark_count'] ?? 0),
                    'video_views'      => 0,
                    'clicks'           => 0,
                    'conversions'      => 0,
                    'engagement_rate'  => $impr > 0 ? round(($likes + $replies + $reposts + $quotes) / $impr * 100, 4) : 0.0,
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
            $credentials = base64_encode(
                config('services.twitter-oauth-2.client_id') . ':' . config('services.twitter-oauth-2.client_secret')
            );

            Http::withHeaders(['Authorization' => "Basic {$credentials}"])
                ->asForm()
                ->post('https://api.twitter.com/2/oauth2/revoke', [
                    'token'           => $account->access_token,
                    'token_type_hint' => 'access_token',
                    'client_id'       => config('services.twitter-oauth-2.client_id'),
                ]);

            $this->logSuccess($account, 'revoked');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
