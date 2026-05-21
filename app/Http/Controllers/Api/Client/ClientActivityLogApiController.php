<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientActivityLog;
use App\Models\ClientWorkspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientActivityLogApiController extends Controller
{
    public function index(Request $request, string $workspaceUuid): JsonResponse
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);

        $logs = ClientActivityLog::with('user')
            ->where('client_workspace_id', $workspace->id)
            ->when($request->input('module'), fn ($q, $m) => $q->where('module', $m))
            ->when($request->input('action'), fn ($q, $a) => $q->where('action', $a))
            ->latest('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($logs);
    }

    public function store(Request $request, string $workspaceUuid): JsonResponse
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);

        $validated = $request->validate([
            'action'      => 'required|string|max:100',
            'module'      => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'properties'  => 'nullable|array',
        ]);

        $log = ClientActivityLog::create(array_merge($validated, [
            'client_workspace_id' => $workspace->id,
            'user_id'             => $request->user()->id,
            'ip_address'          => $request->ip(),
            'user_agent'          => $request->userAgent(),
            'created_at'          => now(),
        ]));

        return response()->json($log, 201);
    }

    private function resolveWorkspace(string $uuid): ClientWorkspace
    {
        $tenant = app('tenant');

        return ClientWorkspace::whereHas('client', fn ($q) => $q->where('agency_id', $tenant->id))
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
