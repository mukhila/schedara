<?php

namespace App\Http\Controllers;

use App\Models\TenantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function select(Request $request): View|RedirectResponse
    {
        $memberships = TenantUser::where('user_id', $request->user()->id)
            ->whereNotNull('joined_at')
            ->with('tenant.plan')
            ->orderByDesc('joined_at')
            ->get();

        // Auto-redirect if only one workspace
        if ($memberships->count() === 1) {
            return $this->switchTo($request, $memberships->first()->tenant_id);
        }

        return view('auth.workspace-select', compact('memberships'));
    }

    public function switchTo(Request $request, int $tenantId): RedirectResponse
    {
        $membership = TenantUser::where('user_id', $request->user()->id)
            ->where('tenant_id', $tenantId)
            ->whereNotNull('joined_at')
            ->first();

        if (!$membership && !$request->user()->is_super_admin) {
            return redirect()->route('workspace.select')
                ->withErrors(['workspace' => 'You are not a member of this workspace.']);
        }

        $request->session()->put('current_tenant_id', $tenantId);

        return redirect()->intended(route('dashboard'));
    }
}
