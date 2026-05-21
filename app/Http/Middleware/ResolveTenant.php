<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // API requests: X-Tenant-ID header
        if ($request->is('api/*')) {
            $tenantId = $request->header('X-Tenant-ID');

            if (!$tenantId) {
                return response()->json(['message' => 'Tenant not specified. Supply X-Tenant-ID header.'], 400);
            }

            return $this->bindTenant((int) $tenantId, $user, $request, $next);
        }

        // Web requests: session
        $tenantId = $request->session()->get('current_tenant_id');

        if (!$tenantId && $user) {
            // Auto-select when user belongs to exactly one tenant
            $memberships = TenantUser::where('user_id', $user->id)
                ->whereNotNull('joined_at')
                ->get();

            if ($memberships->count() === 1) {
                $tenantId = $memberships->first()->tenant_id;
                $request->session()->put('current_tenant_id', $tenantId);
            } else {
                return redirect()->route('workspace.select');
            }
        }

        if (!$tenantId) {
            return redirect()->route('workspace.select');
        }

        return $this->bindTenant((int) $tenantId, $user, $request, $next);
    }

    private function bindTenant(int $tenantId, $user, Request $request, Closure $next): Response
    {
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $request->session()->forget('current_tenant_id');
            return redirect()->route('workspace.select')
                ->withErrors(['workspace' => 'Workspace not found.']);
        }

        if ($user) {
            $membership = TenantUser::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->whereNotNull('joined_at')
                ->first();

            if (!$membership && !$user->is_super_admin) {
                $request->session()->forget('current_tenant_id');

                if ($request->is('api/*')) {
                    return response()->json(['message' => 'Access to this workspace denied.'], 403);
                }

                return redirect()->route('workspace.select')
                    ->withErrors(['workspace' => 'You are not a member of this workspace.']);
            }

            app()->instance('current.tenant.membership', $membership);
        }

        app()->instance('current.tenant.id', $tenant->id);
        app()->instance('current.tenant', $tenant);

        return $next($request);
    }
}
