<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('mfa_pending_user_id')) {
            return redirect()->route('auth.mfa.challenge');
        }

        return $next($request);
    }
}
