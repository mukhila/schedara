<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showForm(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->password || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            RegisterController::issueOtp($user);
            $request->session()->put('pending_email', $user->email);
            return redirect()->route('auth.verify-email')
                ->with('info', 'Please verify your email first. A new code was sent.');
        }

        if ($user->mfa_enabled) {
            $request->session()->put('mfa_pending_user_id', $user->id);
            $request->session()->put('mfa_pending_remember', $request->boolean('remember'));
            return redirect()->route('auth.mfa.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        if ($token = $request->session()->get('pending_invitation_token')) {
            return redirect()->route('invitation.show', ['token' => $token]);
        }

        return redirect()->intended(route('dashboard'));
    }

    /** Passwordless email OTP — handles both login and registration. */
    public function sendEmailOtp(Request $request, TenantService $tenantService): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'name'  => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Derive a readable name from email prefix if not supplied
            $name = $request->filled('name')
                ? $request->name
                : ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $request->email)[0]));

            $user = User::create([
                'name'     => $name,
                'email'    => $request->email,
                'password' => null,
            ]);

            $tenant = $tenantService->createForUser($user);
            $request->session()->put('current_tenant_id', $tenant->id);
        }

        RegisterController::issueOtp($user);
        $request->session()->put('pending_email', $user->email);

        return redirect()->route('auth.verify-email');
    }

    /** AJAX: check if an email already has an account. */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        return response()->json([
            'exists' => User::where('email', $request->email)->exists(),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
