<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;
use App\Models\SocialLog;
use App\Services\Social\Contracts\SocialServiceInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseSocialService implements SocialServiceInterface
{
    protected function http(SocialAccount $account, string $baseUrl = ''): PendingRequest
    {
        return Http::withToken($account->access_token)
            ->baseUrl($baseUrl)
            ->timeout(30)
            ->retry(2, 500);
    }

    protected function get(SocialAccount $account, string $url, array $params = []): array
    {
        $response = $this->http($account)->get($url, $params);

        if ($response->failed()) {
            $this->logError($account, 'api_request', $response->json() ?? [], $response->status());
            $response->throw();
        }

        return $response->json();
    }

    protected function post(SocialAccount $account, string $url, array $data = []): array
    {
        $response = $this->http($account)->post($url, $data);

        if ($response->failed()) {
            $this->logError($account, 'api_request', $response->json() ?? [], $response->status());
            $response->throw();
        }

        return $response->json();
    }

    protected function postJson(SocialAccount $account, string $url, array $data = []): array
    {
        $response = $this->http($account)->asJson()->post($url, $data);

        if ($response->failed()) {
            $this->logError($account, 'api_request', $response->json() ?? [], $response->status());
            $response->throw();
        }

        return $response->json();
    }

    protected function downloadMedia(string $url): string
    {
        $response = Http::timeout(60)->get($url);
        if ($response->failed()) {
            throw new \RuntimeException("Failed to download media (HTTP {$response->status()}): {$url}");
        }
        return $response->body();
    }

    protected function logSuccess(SocialAccount $account, string $action, array $data = []): void
    {
        SocialLog::record($account, $action, 'success', $data);
    }

    protected function logError(SocialAccount $account, string $action, array $data = [], int $statusCode = 0): void
    {
        $error = "HTTP {$statusCode}: " . ($data['error']['message'] ?? $data['message'] ?? json_encode($data));
        SocialLog::record($account, $action, 'failure', $data, $error);
        Log::warning("Social API error [{$this->platform()}] {$action}", ['error' => $error, 'account_id' => $account->id]);
    }

    /**
     * Default publishPost stub — each platform service must override this
     * with real API calls once credentials are configured.
     */
    public function publishPost(Post $post, PostPlatformConfig $config): string
    {
        throw new \RuntimeException(
            "Publishing to [{$this->platform()}] is not yet implemented. " .
            "Override publishPost() in " . static::class . " with the platform's Content Publishing API."
        );
    }

    /** Default no-op analytics fetch; each platform service overrides this. */
    public function fetchAnalytics(SocialAccount $account): array
    {
        return [];
    }

    /** Default no-op refresh; platforms that support it override this. */
    public function refreshToken(SocialAccount $account): SocialAccount
    {
        return $account;
    }

    /** Default no-op revoke; platforms that support it override this. */
    public function revokeToken(SocialAccount $account): bool
    {
        return true;
    }

    /** Default: no sub-pages for most platforms. */
    public function getPages(SocialAccount $account): array
    {
        return [];
    }

    /**
     * Fetch inbox messages (DMs, comments, mentions) from the platform.
     * Returns array of normalized message arrays:
     * [external_id, type, from_user, content, received_at, status, sentiment?]
     * Platform services override this to call their API.
     */
    public function fetchInbox(SocialAccount $account): array
    {
        return [];
    }

    /**
     * Reply to a message/comment on the platform.
     * Platform services override this to call their API.
     */
    public function replyToMessage(SocialAccount $account, string $externalId, string $text): void
    {
        throw new \RuntimeException(
            "Reply not supported on [{$this->platform()}]. Override replyToMessage() in " . static::class . '.'
        );
    }
}
