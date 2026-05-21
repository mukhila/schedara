<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(TenantService $tenantService): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
        } else {
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password'          => null,
            ]);

            // Create personal workspace for new OAuth users
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
