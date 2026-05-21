<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpVerificationNotification;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showForm(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, TenantService $tenantService): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        // Create personal workspace immediately; user won't access it until email verified
        $tenant = $tenantService->createForUser($user);

        // Stash tenant in session so ResolveTenant can bind it after verification
        $request->session()->put('current_tenant_id', $tenant->id);

        self::issueOtp($user);

        $request->session()->put('pending_email', $user->email);

        return redirect()->route('auth.verify-email')
            ->with('success', 'Account created! Check your email for the verification code.');
    }

    public static function issueOtp(User $user): void
    {
        OtpCode::where('user_id', $user->id)->where('type', 'email_verification')->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'    => $user->id,
            'type'       => 'email_verification',
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new OtpVerificationNotification($code, $user->name));
    }
}
