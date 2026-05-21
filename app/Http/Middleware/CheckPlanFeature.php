<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! app()->bound('current.tenant')) {
            abort(403, 'No active workspace.');
        }

        $tenant = app('current.tenant');
        $plan   = $tenant->plan;

        if (! $plan || ! $plan->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Your plan does not include the \"{$feature}\" feature.",
                    'upgrade' => route('billing.plans'),
                ], 403);
            }

            return redirect()->route('billing.plans')
                ->with('error', "Upgrade your plan to access {$feature}.");
        }

        return $next($request);
    }
}
