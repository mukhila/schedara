<?php

namespace App\Http\Middleware;

use App\Models\ClientWorkspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveClientWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceUuid = $request->header('X-Client-Workspace') ?? $request->query('workspace');

        if ($workspaceUuid) {
            $workspace = ClientWorkspace::where('uuid', $workspaceUuid)
                ->where('status', 'active')
                ->first();

            if ($workspace) {
                $request->attributes->set('client_workspace', $workspace);
                app()->instance('client_workspace', $workspace);
            }
        }

        return $next($request);
    }
}
