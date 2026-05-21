<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function callback(TenantService $tenantService): RedirectResponse
    {
        try {
            $msUser = Socialite::driver('microsoft')->user();
        } catch (\Throwable) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'Microsoft authentication failed. Please try again.']);
        }

        $email = $msUser->getEmail();
        if (!$email) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'No email address returned from Microsoft.']);
        }

        $user = User::where('microsoft_id', $msUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if (!$user->microsoft_id) {
                $user->update(['microsoft_id' => $msUser->getId()]);
            }
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
        } else {
            $user = User::create([
                'name'              => $msUser->getName() ?: explode('@', $email)[0],
                'email'             => $email,
                'microsoft_id'      => $msUser->getId(),
                'avatar'            => $msUser->getAvatar(),
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
