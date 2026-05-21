<?php

namespace Tests\Feature\Auth;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoginOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_verify_form_is_shown(): void
    {
        $this->get(route('auth.verify-email'))
            ->assertOk()
            ->assertViewIs('auth.verify-email');
    }

    public function test_valid_otp_logs_user_in(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $otp = OtpCode::create([
            'user_id'    => $user->id,
            'code'       => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->withSession(['pending_email' => $user->email])
            ->post(route('auth.verify-email.post'), ['code' => '123456'])
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_expired_otp_is_rejected(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => '999999',
            'expires_at' => now()->subMinutes(1),
        ]);

        $this->withSession(['pending_email' => $user->email])
            ->post(route('auth.verify-email.post'), ['code' => '999999'])
            ->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_wrong_otp_code_is_rejected(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => '111111',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->withSession(['pending_email' => $user->email])
            ->post(route('auth.verify-email.post'), ['code' => '000000'])
            ->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_otp_resend_requires_pending_session(): void
    {
        $this->post(route('auth.verify-email.resend'))
            ->assertRedirect();
    }

    public function test_email_otp_sends_code_to_existing_user(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->post(route('auth.email-otp'), [
            'email' => $user->email,
        ])->assertRedirectContains('verify-email');

        $this->assertDatabaseHas('otp_codes', ['user_id' => $user->id]);
    }
}
