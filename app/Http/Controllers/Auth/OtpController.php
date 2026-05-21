<?php

namespace App\Http\Controllers\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function showForm(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('pending_email');

        if (!$email) {
            return redirect()->route('auth.login');
        }

        $user = User::where('email', $email)->first();

        // Calculate cooldown remaining so the view can show a countdown
        $cooldownSeconds = 0;
        if ($user) {
            $recent = OtpCode::where('user_id', $user->id)
                ->where('type', 'email_verification')
                ->where('created_at', '>', now()->subSeconds(60))
                ->orderByDesc('created_at')
                ->first();

            if ($recent) {
                // Use explicit signed diff: seconds since OTP was created
                $secondsSinceCreated = max(0, (int) $recent->created_at->diffInSeconds(now(), false));
                $cooldownSeconds = max(0, 60 - $secondsSinceCreated);
            }
        }

        return view('auth.verify-email', [
            'email'           => $email,
            'cooldownSeconds' => $cooldownSeconds,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:6']);

        $email = $request->session()->get('pending_email');

        if (!$email) {
            return redirect()->route('auth.login');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('auth.login');
        }

        $otp = OtpCode::where('user_id', $user->id)
            ->where('type', 'email_verification')
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        $otp->update(['used_at' => now()]);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $request->session()->forget('pending_email');

        if ($user->mfa_enabled) {
            $request->session()->put('mfa_pending_user_id', $user->id);
            $request->session()->put('mfa_pending_remember', false);
            return redirect()->route('auth.mfa.challenge');
        }

        Auth::login($user);
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welcome to Schedara!');
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('pending_email');

        if (!$email) {
            return redirect()->route('auth.login');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('auth.login');
        }

        $recentExists = OtpCode::where('user_id', $user->id)
            ->where('type', 'email_verification')
            ->where('created_at', '>', now()->subSeconds(60))
            ->exists();

        if ($recentExists) {
            return back()->withErrors(['resend' => 'Please wait 60 seconds before requesting a new code.']);
        }

        RegisterController::issueOtp($user);

        return back()->with('success', 'A new verification code has been sent.');
    }
}
