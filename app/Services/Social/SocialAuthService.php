<?php

namespace App\Services\Social;

use App\DTOs\Social\SocialAccountDTO;
use App\DTOs\Social\SocialPageDTO;
use App\Events\Social\SocialAccountConnected;
use App\Events\Social\SocialAccountDisconnected;
use App\Events\Social\TokenExpired;
use App\Exceptions\Social\UnsupportedPlatformException;
use App\Models\SocialAccount;
use App\Models\SocialLog;
use App\Models\SocialPage;
use App\Models\SocialPlatform;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Social\Contracts\SocialServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class SocialAuthService
{
    /** Maps platform slug → Socialite driver name */
    private const DRIVER_MAP = [
        'facebook'  => 'facebook',
        'instagram' => 'facebook',
        'linkedin'  => 'linkedin-openid',
        'twitter'   => 'twitter-oauth-2',
        'pinterest' => 'pinterest',
        'youtube'   => 'google',
        'threads'   => 'threads',
        // Requires: composer require socialiteproviders/tiktok
        'tiktok'    => 'tiktok',
    ];

    /** Extra scopes needed for social management (beyond defaults in config) */
    private const SCOPES_MAP = [
        'facebook'  => ['pages_show_list', 'pages_read_engagement', 'pages_manage_posts', 'pages_manage_metadata', 'read_insights'],
        'instagram' => ['instagram_basic', 'instagram_content_publish', 'instagram_manage_insights', 'instagram_manage_comments', 'pages_show_list'],
        'linkedin'  => ['openid', 'profile', 'email', 'w_member_social', 'r_organization_social'],
        'twitter'   => ['tweet.read', 'tweet.write', 'users.read', 'offline.access'],
        'pinterest' => ['boards:read', 'boards:write', 'pins:read', 'pins:write', 'user_accounts:read'],
        'youtube'   => ['openid', 'profile', 'email', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube.upload'],
        'threads'   => ['threads_basic', 'threads_content_publish', 'threads_manage_insights'],
        'tiktok'    => ['user.info.basic', 'video.list', 'video.upload', 'video.publish'],
    ];

    // ── OAuth Redirect ───────────────────────────────────────────

    public function getRedirectUrl(string $platform): string
    {
        $driver = $this->resolveDriver($platform);
        $scopes = self::SCOPES_MAP[$platform] ?? [];
        $state  = $this->generateState($platform);

        $socialite = Socialite::driver($driver)
            ->scopes($scopes)
            ->with(['state' => $state]);

        // Twitter/X requires offline.access via the 'offline_access' parameter
        if ($platform === 'twitter') {
            $socialite = $socialite->with(['state' => $state, 'force_login' => 'true']);
        }

        // YouTube needs offline access for refresh tokens
        if ($platform === 'youtube') {
            $socialite = $socialite->with([
                'access_type'    => 'offline',
                'prompt'         => 'consent',
                'state'          => $state,
            ]);
        }

        return $socialite->stateless()->redirect()->getTargetUrl();
    }

    // ── OAuth Callback ───────────────────────────────────────────

    public function handleCallback(string $platform, User $user, Tenant $tenant): SocialAccount
    {
        $this->verifyState($platform);

        $driver      = $this->resolveDriver($platform);
        $socialUser  = Socialite::driver($driver)->stateless()->user();
        $dto         = SocialAccountDTO::fromSocialite($socialUser);
        $platformRow = SocialPlatform::where('slug', $platform)->firstOrFail();

        return DB::transaction(function () use ($dto, $platform, $platformRow, $user, $tenant) {
            $account = SocialAccount::withTrashed()->updateOrCreate(
                [
                    'tenant_id'        => $tenant->id,
                    'platform_id'      => $platformRow->id,
                    'platform_user_id' => $dto->platformUserId,
                ],
                array_merge($dto->toArray(), [
                    'user_id'    => $user->id,
                    'deleted_at' => null,
                ])
            );

            // Sync pages if platform supports them
            if ($platformRow->hasCapability('pages') || $platformRow->hasCapability('channels') || $platformRow->hasCapability('boards')) {
                $this->syncPages($account);
            }

            SocialLog::record($account, 'connected', 'success');
            event(new SocialAccountConnected($account));

            return $account->load('platform', 'pages');
        });
    }

    // ── Page Sync ────────────────────────────────────────────────

    public function syncPages(SocialAccount $account): array
    {
        $service = $this->resolveService($account->platform->slug);
        $pages   = $service->getPages($account);

        foreach ($pages as $dto) {
            /** @var SocialPageDTO $dto */
            SocialPage::updateOrCreate(
                ['social_account_id' => $account->id, 'page_id' => $dto->pageId],
                $dto->toArray()
            );
        }

        $account->update(['last_synced_at' => now()]);
        SocialLog::record($account, 'pages_synced', 'success', ['count' => count($pages)]);

        return $pages;
    }

    // ── Token Refresh ────────────────────────────────────────────

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        $service = $this->resolveService($account->platform->slug);
        $updated = $service->refreshToken($account);

        if ($updated->status === 'expired') {
            event(new TokenExpired($updated));
        } else {
            SocialLog::record($updated, 'token_refreshed', 'success');
        }

        return $updated;
    }

    // ── Disconnect ───────────────────────────────────────────────

    public function disconnect(SocialAccount $account): void
    {
        $service = $this->resolveService($account->platform->slug);
        $service->revokeToken($account);

        $account->pages()->delete();
        $account->delete();

        event(new SocialAccountDisconnected($account));
    }

    // ── Profile Sync ─────────────────────────────────────────────

    public function syncProfile(SocialAccount $account): SocialAccount
    {
        $service = $this->resolveService($account->platform->slug);
        $profile = $service->getProfile($account);

        $account->update([
            'account_name'   => $profile['name'] ?? $account->account_name,
            'avatar'         => $profile['picture']['data']['url'] ?? $profile['profile_image_url'] ?? $account->avatar,
            'last_synced_at' => now(),
        ]);

        SocialLog::record($account, 'profile_synced', 'success');
        return $account->fresh();
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function resolveDriver(string $platform): string
    {
        return self::DRIVER_MAP[$platform] ?? throw new UnsupportedPlatformException($platform);
    }

    public function getService(string $platform): SocialServiceInterface
    {
        return $this->resolveService($platform);
    }

    private function resolveService(string $platform): SocialServiceInterface
    {
        return match ($platform) {
            'facebook'  => app(FacebookService::class),
            'instagram' => app(InstagramService::class),
            'linkedin'  => app(LinkedInService::class),
            'twitter'   => app(TwitterService::class),
            'pinterest' => app(PinterestService::class),
            'youtube'   => app(YouTubeService::class),
            'threads'   => app(ThreadsService::class),
            'tiktok'    => app(TikTokService::class),
            default     => throw new UnsupportedPlatformException($platform),
        };
    }

    private function generateState(string $platform): string
    {
        $state = Str::random(40);
        session()->put("social_oauth_state_{$platform}", $state);
        return $state;
    }

    private function verifyState(string $platform): void
    {
        $returned = request()->get('state');
        $saved    = session()->pull("social_oauth_state_{$platform}");

        if (! $returned || ! $saved || ! hash_equals($saved, $returned)) {
            abort(422, 'Invalid OAuth state. Please try again.');
        }
    }
}
