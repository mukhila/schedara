<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($data);
        RegisterController::issueOtp($user);

        return response()->json([
            'message' => 'Account created. Check your email for the verification code.',
            'email'   => $user->email,
        ], 201);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $otp = OtpCode::where('user_id', $user->id)
            ->where('type', 'email_verification')
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        $otp->update(['used_at' => now()]);
        $user->markEmailAsVerified();
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('api', ['*'], now()->addDays(30));

        return response()->json([
            'message' => 'Email verified.',
            'token'   => $token->plainTextToken,
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->password || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        if (!$user->hasVerifiedEmail()) {
            RegisterController::issueOtp($user);
            return response()->json(['message' => 'Email not verified. A new code was sent.', 'requires_verification' => true], 403);
        }

        if ($user->mfa_enabled) {
            // Issue short-lived pre-auth token; client must verify TOTP next
            $preToken = $user->createToken('mfa-pending', ['mfa:verify'], now()->addMinutes(5));
            return response()->json([
                'mfa_required'  => true,
                'pre_auth_token' => $preToken->plainTextToken,
            ]);
        }

        $user->tokens()->where('name', 'api')->delete();
        $token = $user->createToken('api', ['*'], now()->addDays(30));
        $user->update(['last_login_at' => now()]);

        return response()->json(['token' => $token->plainTextToken]);
    }

    public function mfaVerify(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = $request->user();

        if (!$user || !$user->mfa_enabled) {
            return response()->json(['message' => 'MFA not enabled.'], 400);
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($user->mfa_secret, $request->code, 2)) {
            return response()->json(['message' => 'Invalid authenticator code.'], 422);
        }

        // Revoke the pre-auth token, issue full token
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('api', ['*'], now()->addDays(30));
        $user->update(['last_login_at' => now()]);

        return response()->json(['token' => $token->plainTextToken]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function forgotPassword(Request $request): JsonResponse
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
                OtpCode::create(['user_id' => $user->id, 'type' => 'password_reset', 'code' => $code, 'expires_at' => now()->addMinutes(15)]);
                $user->notify(new PasswordResetOtpNotification($code));
            }
        }

        return response()->json(['message' => 'If that email exists, a reset code was sent.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid reset request.'], 422);
        }

        $otp = OtpCode::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        $otp->update(['used_at' => now()]);
        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function sessions(Request $request): JsonResponse
    {
        $tokens = PersonalAccessToken::where('tokenable_id', $request->user()->id)
            ->where('tokenable_type', get_class($request->user()))
            ->orderByDesc('last_used_at')
            ->get(['id', 'name', 'last_used_at', 'created_at', 'expires_at']);

        return response()->json(['sessions' => $tokens]);
    }

    public function revokeSession(Request $request, int $tokenId): JsonResponse
    {
        PersonalAccessToken::where('tokenable_id', $request->user()->id)
            ->where('id', $tokenId)
            ->delete();

        return response()->json(['message' => 'Session revoked.']);
    }

    public function revokeAllSessions(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'All sessions revoked.']);
    }
}
