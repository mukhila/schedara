<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\TenantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(Request $request, string $token): View|RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return view('auth.invitation', ['state' => 'expired', 'invitation' => null]);
        }

        $invitation = TeamInvitation::with(['tenant', 'inviter'])
            ->where('token', $token)
            ->first();

        if (!$invitation || !$invitation->isPending()) {
            return view('auth.invitation', ['state' => 'expired', 'invitation' => $invitation]);
        }

        if (!Auth::check()) {
            $request->session()->put('pending_invitation_token', $token);
            return redirect()->route('auth.login')
                ->with('info', "You've been invited to {$invitation->tenant->name}. Sign in or create an account to accept.");
        }

        $currentUser = Auth::user();

        if ($currentUser->email !== $invitation->email) {
            return view('auth.invitation', [
                'state'      => 'mismatch',
                'invitation' => $invitation,
            ]);
        }

        return view('auth.invitation', [
            'state'      => 'pending',
            'invitation' => $invitation,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = TeamInvitation::with('tenant')
            ->where('token', $token)
            ->firstOrFail();

        abort_unless($invitation->isPending(), 410, 'This invitation is no longer valid.');
        abort_unless(Auth::user()->email === $invitation->email, 403, 'This invitation is for a different email address.');

        $alreadyMember = TenantUser::where('tenant_id', $invitation->tenant_id)
            ->where('user_id', Auth::id())
            ->whereNotNull('joined_at')
            ->exists();

        if (!$alreadyMember) {
            TenantUser::create([
                'tenant_id'  => $invitation->tenant_id,
                'user_id'    => Auth::id(),
                'role'       => $invitation->role,
                'invited_at' => $invitation->created_at,
                'joined_at'  => now(),
            ]);
        }

        $invitation->update(['accepted_at' => now()]);

        $request->session()->put('current_tenant_id', $invitation->tenant_id);
        $request->session()->forget('pending_invitation_token');

        return redirect()->route('dashboard')
            ->with('success', "Welcome to {$invitation->tenant->name}!");
    }

    public function decline(Request $request, string $token): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        abort_unless($invitation->isPending(), 410, 'This invitation is no longer valid.');

        $invitation->update(['declined_at' => now()]);
        $request->session()->forget('pending_invitation_token');

        return redirect()->route('home')
            ->with('info', 'Invitation declined.');
    }
}
