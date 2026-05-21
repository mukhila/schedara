<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $tooRecent = OtpCode::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->where('created_at', '>', now()->subSeconds(60))
                ->exists();

            if (!$tooRecent) {
                OtpCode::where('user_id', $user->id)->where('type', 'password_reset')->delete();

                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                OtpCode::create([
                    'user_id'    => $user->id,
                    'type'       => 'password_reset',
                    'code'       => $code,
                    'expires_at' => now()->addMinutes(15),
                ]);

                $user->notify(new PasswordResetOtpNotification($code));
            }
        }

        $request->session()->put('reset_email', $request->email);

        return redirect()->route('auth.reset-password')
            ->with('success', 'If that email is registered, a reset code was sent.');
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('reset_email')) {
            return redirect()->route('auth.forgot-password');
        }

        return view('auth.reset-password', [
            'email' => $request->session()->get('reset_email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'code'     => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->session()->get('reset_email');

        if (!$email) {
            return redirect()->route('auth.forgot-password');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('auth.forgot-password');
        }

        $otp = OtpCode::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'Invalid or expired reset code.']);
        }

        $otp->update(['used_at' => now()]);
        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();

        $request->session()->forget('reset_email');

        return redirect()->route('auth.login')
            ->with('success', 'Password reset successfully. You can now sign in.');
    }
}
