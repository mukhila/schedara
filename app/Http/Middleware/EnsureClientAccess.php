<?php

namespace App\Http\Middleware;

use App\Models\AgencyClient;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app('tenant');
        $client = $request->route('client');

        if (!$client instanceof AgencyClient) {
            $client = AgencyClient::where('uuid', $client)->first();
        }

        if (!$client || $client->agency_id !== $tenant->id) {
            return response()->json(['message' => 'Client not found.'], 404);
        }

        $request->attributes->set('agency_client', $client);

        return $next($request);
    }
}
