<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimit
{
    /**
     * Usage: Route::middleware('plan.limit:social_accounts,5')
     * The middleware signature: plan.limit:{key},{model?}
     * It compares current count against plan limit.
     */
    public function handle(Request $request, Closure $next, string $limitKey, ?string $modelClass = null): Response
    {
        if (! app()->bound('current.tenant')) {
            return $next($request);
        }

        $tenant = app('current.tenant');
        $plan   = $tenant->plan;

        if (! $plan) {
            return $next($request);
        }

        $limit = $plan->getLimit($limitKey);

        if ($limit === null || $limit === -1) {
            return $next($request);
        }

        $count = $this->resolveCount($tenant, $limitKey, $modelClass);

        if ($count >= $limit) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "You have reached the limit for {$limitKey} ({$limit}).",
                    'upgrade' => route('billing.plans'),
                ], 403);
            }

            return redirect()->back()
                ->with('error', "You have reached your plan limit for {$limitKey}. Upgrade to add more.");
        }

        return $next($request);
    }

    private function resolveCount(\App\Models\Tenant $tenant, string $limitKey, ?string $modelClass): int
    {
        $map = [
            'team_members'    => fn () => $tenant->memberships()->whereNotNull('joined_at')->count(),
            'social_accounts' => fn () => $tenant->socialAccounts()->count(),
            'posts_per_month' => fn () => $tenant->posts()->whereMonth('created_at', now()->month)->count(),
            'media_storage'   => fn () => 0, // implement with storage usage tracking
        ];

        return isset($map[$limitKey]) ? ($map[$limitKey])() : 0;
    }
}
