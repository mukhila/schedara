<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Gate::allows($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
