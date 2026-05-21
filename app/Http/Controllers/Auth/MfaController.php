<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class MfaController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // ── Login challenge ───────────────────────────────────────────────

    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('mfa_pending_user_id')) {
            return redirect()->route('auth.login');
        }

        return view('auth.mfa-challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:6']);

        $userId = $request->session()->get('mfa_pending_user_id');

        if (!$userId) {
            return redirect()->route('auth.login');
        }

        $user = User::find($userId);

        if (!$user || !$user->mfa_enabled) {
            return redirect()->route('auth.login');
        }

        if (!$this->google2fa->verifyKey($user->mfa_secret, $request->code, 2)) {
            return back()->withErrors(['code' => 'Invalid authenticator code. Please try again.']);
        }

        $remember = $request->session()->pull('mfa_pending_remember', false);
        $request->session()->forget('mfa_pending_user_id');

        Auth::login($user, $remember);
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // ── Setup / manage (authenticated) ───────────────────────────────

    public function showSetup(Request $request): View
    {
        $user = $request->user();

        if ($user->mfa_enabled) {
            return view('auth.mfa-setup', ['enabled' => true, 'secret' => null, 'qrSvg' => null, 'segments' => null]);
        }

        if (!$request->session()->has('mfa_temp_secret')) {
            $request->session()->put('mfa_temp_secret', $this->google2fa->generateSecretKey());
        }

        $secret = $request->session()->get('mfa_temp_secret');
        $qrUrl  = $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);

        return view('auth.mfa-setup', [
            'enabled'  => false,
            'secret'   => $secret,
            'segments' => implode(' ', str_split($secret, 4)),
            'qrSvg'    => $this->buildQrSvg($qrUrl),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:6']);

        $secret = $request->session()->get('mfa_temp_secret');

        if (!$secret) {
            return redirect()->route('auth.mfa.setup')
                ->withErrors(['code' => 'Session expired. Please start the setup again.']);
        }

        if (!$this->google2fa->verifyKey($secret, $request->code, 2)) {
            return back()->withErrors(['code' => 'Invalid code. Make sure your authenticator app is synced.']);
        }

        $request->user()->update(['mfa_enabled' => true, 'mfa_secret' => $secret]);
        $request->session()->forget('mfa_temp_secret');

        return redirect()->route('auth.mfa.setup')
            ->with('success', 'Two-factor authentication is now enabled on your account.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'code'     => 'required|digits:6',
            'password' => 'required|current_password',
        ]);

        $user = $request->user();

        if (!$this->google2fa->verifyKey($user->mfa_secret, $request->code, 2)) {
            return back()->withErrors(['code' => 'Invalid authenticator code.']);
        }

        $user->update(['mfa_enabled' => false, 'mfa_secret' => null]);

        return redirect()->route('auth.mfa.setup')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    private function buildQrSvg(string $url): string
    {
        $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());
        return (new Writer($renderer))->writeString($url);
    }
}
