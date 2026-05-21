<?php

namespace Tests\Feature\Social;

use App\Models\SocialAccount;
use App\Models\SocialPlatform;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsVerifiedUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        Tenant::factory()->create(['owner_id' => $user->id]);

        return $user;
    }

    public function test_social_connect_redirects_to_platform_oauth(): void
    {
        $this->actingAsVerifiedUser();

        SocialPlatform::firstOrCreate(['slug' => 'facebook'], ['name' => 'Facebook']);

        Socialite::shouldReceive('driver')
            ->with('facebook')
            ->andReturnSelf();
        Socialite::shouldReceive('scopes')
            ->andReturnSelf();
        Socialite::shouldReceive('redirect')
            ->andReturn(redirect('https://facebook.com/oauth/authorize?client_id=test'));

        $this->get(route('social.connect', ['platform' => 'facebook']))
            ->assertRedirect();
    }

    public function test_social_callback_creates_social_account(): void
    {
        $user = $this->actingAsVerifiedUser();

        $platform = SocialPlatform::firstOrCreate(['slug' => 'facebook'], ['name' => 'Facebook']);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->id           = 'fb_user_123';
        $socialiteUser->name         = 'Test User';
        $socialiteUser->email        = $user->email;
        $socialiteUser->token        = 'access_token_abc';
        $socialiteUser->refreshToken = null;
        $socialiteUser->expiresIn    = 3600;
        $socialiteUser->avatar       = 'https://graph.facebook.com/fb_user_123/picture';

        Socialite::shouldReceive('driver')
            ->with('facebook')
            ->andReturnSelf();
        Socialite::shouldReceive('stateless')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);

        $this->get(route('social.callback', ['platform' => 'facebook']))
            ->assertRedirect();

        $this->assertDatabaseHas('social_accounts', [
            'platform_id'      => $platform->id,
            'platform_user_id' => 'fb_user_123',
        ]);
    }

    public function test_social_connect_requires_authentication(): void
    {
        SocialPlatform::firstOrCreate(['slug' => 'facebook'], ['name' => 'Facebook']);

        $this->get(route('social.connect', ['platform' => 'facebook']))
            ->assertRedirect(route('auth.login'));
    }

    public function test_social_account_is_updated_on_reconnect(): void
    {
        $user     = $this->actingAsVerifiedUser();
        $platform = SocialPlatform::firstOrCreate(['slug' => 'facebook'], ['name' => 'Facebook']);

        SocialAccount::factory()->create([
            'platform_id'      => $platform->id,
            'platform_user_id' => 'fb_user_123',
            'access_token'     => 'old_token',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->id           = 'fb_user_123';
        $socialiteUser->name         = 'Test User';
        $socialiteUser->email        = $user->email;
        $socialiteUser->token        = 'new_token_xyz';
        $socialiteUser->refreshToken = null;
        $socialiteUser->expiresIn    = 3600;
        $socialiteUser->avatar       = null;

        Socialite::shouldReceive('driver')->with('facebook')->andReturnSelf();
        Socialite::shouldReceive('stateless')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($socialiteUser);

        $this->get(route('social.callback', ['platform' => 'facebook']));

        $this->assertDatabaseHas('social_accounts', [
            'platform_user_id' => 'fb_user_123',
            'access_token'     => 'new_token_xyz',
        ]);
        $this->assertDatabaseCount('social_accounts', 1);
    }
}
