<?php

namespace App\Services\Social;

use App\DTOs\Social\SocialPageDTO;
use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class YouTubeService extends BaseSocialService
{
    private const API = 'https://www.googleapis.com/youtube/v3';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public function platform(): string { return 'youtube'; }

    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        $account  = $config->socialAccount;
        $media    = $post->media()->where('media_type', 'video')->orderBy('sort_order')->first()
            ?? $post->media()->orderBy('sort_order')->first();

        if (! $media) {
            throw new \RuntimeException('YouTube requires a video file.');
        }

        $title       = mb_substr($post->title ?? $config->effectiveContent(), 0, 100);
        $description = mb_substr($config->effectiveContent(), 0, 5000);
        $mimeType    = $media->mime_type ?? 'video/mp4';

        // Step 1: initiate resumable upload session
        $initResp = Http::withToken($account->access_token)
            ->withHeaders(['X-Upload-Content-Type' => $mimeType])
            ->timeout(30)
            ->asJson()
            ->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status', [
                'snippet' => [
                    'title'       => $title,
                    'description' => $description,
                    'categoryId'  => '22',
                ],
                'status' => [
                    'privacyStatus'           => 'public',
                    'selfDeclaredMadeForKids' => false,
                ],
            ]);

        if ($initResp->failed()) {
            throw new \RuntimeException('YouTube upload session failed: ' . $initResp->body());
        }

        $uploadUrl = $initResp->header('Location');
        if (! $uploadUrl) {
            throw new \RuntimeException('YouTube did not return an upload URL.');
        }

        // Step 2: upload the video binary
        $content = $this->downloadMedia($media->publicUrl());

        $uploadResp = Http::withToken($account->access_token)
            ->withHeaders(['Content-Type' => $mimeType])
            ->timeout(300)
            ->withBody($content, $mimeType)
            ->put($uploadUrl);

        if ($uploadResp->failed()) {
            throw new \RuntimeException('YouTube video upload failed: ' . $uploadResp->body());
        }

        return (string) $uploadResp->json('id');
    }

    public function getProfile(SocialAccount $account): array
    {
        return $this->get($account, self::API . '/channels', [
            'part' => 'snippet,statistics',
            'mine' => 'true',
        ]);
    }

    public function getPages(SocialAccount $account): array
    {
        $data = $this->get($account, self::API . '/channels', [
            'part' => 'snippet,statistics',
            'mine' => 'true',
        ]);

        return collect($data['items'] ?? [])->map(fn ($ch) => new SocialPageDTO(
            pageId:         $ch['id'],
            pageName:       $ch['snippet']['title'] ?? 'YouTube Channel',
            pageType:       'channel',
            category:       'YouTube Channel',
            avatar:         $ch['snippet']['thumbnails']['default']['url'] ?? null,
            accessToken:    null,
            followersCount: (int) ($ch['statistics']['subscriberCount'] ?? 0),
            metadata:       $ch,
        ))->values()->all();
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if (! $account->refresh_token) {
            $account->markExpired();
            return $account;
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $account->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if ($response->failed()) {
            $account->markExpired();
            return $account;
        }

        $data = $response->json();
        $account->update([
            'access_token'     => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            'status'           => 'active',
        ]);

        $this->logSuccess($account, 'token_refreshed');
        return $account->fresh();
    }

    public function fetchAnalytics(SocialAccount $account): array
    {
        try {
            // Step 1: get the uploads playlist ID for this channel
            $channelResp = $this->get($account, self::API . '/channels', [
                'part' => 'contentDetails',
                'mine' => 'true',
            ]);

            $uploadsId = $channelResp['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;
            if (! $uploadsId) {
                return [];
            }

            // Step 2: get recent video IDs from the uploads playlist
            $playlistResp = $this->get($account, self::API . '/playlistItems', [
                'part'       => 'contentDetails',
                'playlistId' => $uploadsId,
                'maxResults' => 50,
            ]);

            $videoIds = implode(',', collect($playlistResp['items'] ?? [])
                ->pluck('contentDetails.videoId')
                ->filter()
                ->all());

            if (empty($videoIds)) {
                return [];
            }

            // Step 3: fetch statistics for those videos
            $videosResp = $this->get($account, self::API . '/videos', [
                'part' => 'statistics',
                'id'   => $videoIds,
            ]);

            return collect($videosResp['items'] ?? [])->map(function ($video) {
                $stats    = $video['statistics'] ?? [];
                $views    = (int) ($stats['viewCount'] ?? 0);
                $likes    = (int) ($stats['likeCount'] ?? 0);
                $comments = (int) ($stats['commentCount'] ?? 0);
                $saves    = (int) ($stats['favoriteCount'] ?? 0);

                return [
                    'platform_post_id' => $video['id'],
                    'post_id'          => null,
                    'impressions'      => $views,
                    'reach'            => $views,
                    'likes'            => $likes,
                    'comments'         => $comments,
                    'shares'           => 0,
                    'saves'            => $saves,
                    'video_views'      => $views,
                    'clicks'           => 0,
                    'conversions'      => 0,
                    'engagement_rate'  => $views > 0 ? round(($likes + $comments) / $views * 100, 4) : 0.0,
                    'ctr'              => 0.0,
                    'spend'            => 0.0,
                    'revenue'          => 0.0,
                ];
            })->values()->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
