<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('facebook')->scopes(['email'])->redirect();
    }

    public function callback(TenantService $tenantService): RedirectResponse
    {
        try {
            $fbUser = Socialite::driver('facebook')->user();
        } catch (\Throwable) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'Facebook authentication failed. Please try again.']);
        }

        $email = $fbUser->getEmail();
        if (!$email) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'No email address returned from Facebook. Please check your Facebook privacy settings.']);
        }

        $user = User::where('facebook_id', $fbUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if (!$user->facebook_id) {
                $user->update(['facebook_id' => $fbUser->getId()]);
            }
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
        } else {
            $user = User::create([
                'name'              => $fbUser->getName(),
                'email'             => $email,
                'facebook_id'       => $fbUser->getId(),
                'avatar'            => $fbUser->getAvatar(),
                'email_verified_at' => now(),
                'password'          => null,
            ]);

            $tenant = $tenantService->createForUser($user);
            request()->session()->put('current_tenant_id', $tenant->id);
        }

        if ($user->mfa_enabled) {
            request()->session()->put('mfa_pending_user_id', $user->id);
            request()->session()->put('mfa_pending_remember', false);
            return redirect()->route('auth.mfa.challenge');
        }

        Auth::login($user, true);
        $user->update(['last_login_at' => now()]);
        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
