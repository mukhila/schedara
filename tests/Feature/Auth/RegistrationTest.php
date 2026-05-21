<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_unified_form(): void
    {
        $this->get(route('auth.login'))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    public function test_email_otp_is_sent_for_new_user(): void
    {
        Mail::fake();

        $this->post(route('auth.email-otp'), [
            'email' => 'newuser@example.com',
        ])->assertRedirectContains('verify-email');

        // New user record should exist (unverified)
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_email_otp_requires_valid_email(): void
    {
        $this->post(route('auth.email-otp'), [
            'email' => 'not-an-email',
        ])->assertSessionHasErrors('email');
    }

    public function test_register_route_redirects_to_login(): void
    {
        $this->get(route('auth.register'))
            ->assertRedirect(route('auth.login'));
    }

    public function test_password_login_works_for_verified_user(): void
    {
        $user = User::factory()->create([
            'password'          => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->post(route('auth.login.post'), [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password'          => bcrypt('correct-password'),
            'email_verified_at' => now(),
        ]);

        $this->post(route('auth.login.post'), [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_password_login_for_unverified_user_redirects_to_otp(): void
    {
        $user = User::factory()->create([
            'password'          => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $this->post(route('auth.login.post'), [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('auth.verify-email'));

        $this->assertGuest();
    }
}
