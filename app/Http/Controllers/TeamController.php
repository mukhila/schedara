<?php

namespace App\Http\Controllers;

use App\Enums\TenantRole;
use App\Models\TeamInvitation;
use App\Models\TenantUser;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(private readonly TenantService $tenantService) {}

    public function index(Request $request): View
    {
        $tenant = app('current.tenant');

        $members = TenantUser::where('tenant_id', $tenant->id)
            ->whereNotNull('joined_at')
            ->with('user')
            ->orderByRaw("FIELD(role,'owner','admin','manager','creator','analyst','client')")
            ->get();

        $invitations = TeamInvitation::where('tenant_id', $tenant->id)
            ->pending()
            ->with('inviter')
            ->orderByDesc('created_at')
            ->get();

        /** @var TenantUser $myMembership */
        $myMembership = app('current.tenant.membership');
        $assignableRoles = $myMembership->roleEnum()->assignableRoles();

        return view('backend.team.index', compact('tenant', 'members', 'invitations', 'assignableRoles'));
    }

    public function invite(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email'   => 'required|email',
            'role'    => 'required|in:' . implode(',', array_map(fn ($r) => $r->value, TenantRole::cases())),
            'message' => 'nullable|string|max:500',
        ]);

        $tenant       = app('current.tenant');
        $myMembership = app('current.tenant.membership');
        $myRole       = $myMembership->roleEnum();

        // Validate inviter can assign this role
        $targetRole = TenantRole::from($request->role);
        if (!in_array($targetRole, $myRole->assignableRoles(), strict: true)) {
            return back()->withErrors(['role' => 'You cannot assign this role.']);
        }

        // Prevent duplicate active membership
        $existingMember = TenantUser::where('tenant_id', $tenant->id)
            ->whereHas('user', fn ($q) => $q->where('email', $request->email))
            ->whereNotNull('joined_at')
            ->exists();

        if ($existingMember) {
            return back()->withErrors(['email' => 'This person is already a member of the workspace.']);
        }

        $invitation = $this->tenantService->invite(
            $tenant,
            $request->user(),
            $request->email,
            $request->role,
            $request->message,
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Invitation sent.', 'invitation' => $invitation]);
        }

        return back()->with('success', "Invitation sent to {$request->email}.");
    }

    public function updateRole(Request $request, int $userId): RedirectResponse|JsonResponse
    {
        $request->validate([
            'role' => 'required|in:' . implode(',', array_map(fn ($r) => $r->value, TenantRole::cases())),
        ]);

        $tenant       = app('current.tenant');
        $myMembership = app('current.tenant.membership');

        $targetMembership = TenantUser::where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Cannot change own role or change an Owner's role (unless super admin)
        if ($targetMembership->user_id === $request->user()->id) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }
        if ($targetMembership->isOwner() && !$request->user()->is_super_admin) {
            return back()->withErrors(['role' => 'The workspace owner role cannot be changed.']);
        }

        $newRole = TenantRole::from($request->role);
        if (!in_array($newRole, $myMembership->roleEnum()->assignableRoles(), strict: true)) {
            return back()->withErrors(['role' => 'You cannot assign this role.']);
        }

        $targetMembership->update(['role' => $newRole->value]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Role updated.']);
        }

        return back()->with('success', 'Role updated successfully.');
    }

    public function removeMember(Request $request, int $userId): RedirectResponse|JsonResponse
    {
        $tenant       = app('current.tenant');
        $myMembership = app('current.tenant.membership');

        if ($userId === $request->user()->id) {
            return back()->withErrors(['member' => 'You cannot remove yourself from the workspace.']);
        }

        $targetMembership = TenantUser::where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($targetMembership->isOwner() && !$request->user()->is_super_admin) {
            return back()->withErrors(['member' => 'The workspace owner cannot be removed.']);
        }

        if (!$myMembership->isAdmin() && !$request->user()->is_super_admin) {
            abort(403);
        }

        $targetMembership->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Member removed.']);
        }

        return back()->with('success', 'Member removed from the workspace.');
    }

    public function cancelInvitation(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $tenant = app('current.tenant');

        TeamInvitation::where('tenant_id', $tenant->id)
            ->where('id', $invitationId)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Invitation cancelled.']);
        }

        return back()->with('success', 'Invitation cancelled.');
    }

    public function resendInvitation(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $tenant = app('current.tenant');

        $invitation = TeamInvitation::where('tenant_id', $tenant->id)
            ->where('id', $invitationId)
            ->pending()
            ->firstOrFail();

        // Re-send by creating a fresh one (extends expiry + new signed URL)
        $fresh = $this->tenantService->invite(
            $tenant,
            $request->user(),
            $invitation->email,
            $invitation->role,
            $invitation->message,
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Invitation resent.', 'invitation' => $fresh]);
        }

        return back()->with('success', "Invitation resent to {$invitation->email}.");
    }
}
