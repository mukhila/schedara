<?php

namespace App\Http\Controllers\Api\Client;

use App\DTOs\Client\CreateClientDTO;
use App\DTOs\Client\UpdateClientDTO;
use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Services\Client\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgencyClientApiController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant  = app('tenant');
        $filters = $request->only(['status', 'search', 'industry', 'per_page']);
        $clients = $this->clientService->listClients($tenant, $filters);

        return response()->json($clients);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_name'    => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'company_name'   => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'website'        => 'nullable|url|max:255',
            'industry'       => 'nullable|string|max:100',
            'timezone'       => 'nullable|string|max:64',
            'workspace_name' => 'nullable|string|max:255',
        ]);

        $tenant = app('tenant');
        $dto    = CreateClientDTO::fromArray($validated);
        $client = $this->clientService->createClient($tenant, $dto);

        return response()->json([
            'message' => 'Client created successfully.',
            'client'  => $client,
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $tenant = app('tenant');
        $client = $this->clientService->listClients($tenant); // just reuse repo

        $client = AgencyClient::with(['workspace.whiteLabelSettings', 'onboardingSteps', 'billing'])
            ->where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        return response()->json($client);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $tenant = app('tenant');
        $client = AgencyClient::where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        $validated = $request->validate([
            'client_name'  => 'sometimes|string|max:255',
            'company_name' => 'sometimes|nullable|string|max:255',
            'email'        => 'sometimes|email|max:255',
            'phone'        => 'sometimes|nullable|string|max:30',
            'website'      => 'sometimes|nullable|url|max:255',
            'industry'     => 'sometimes|nullable|string|max:100',
            'timezone'     => 'sometimes|string|max:64',
            'status'       => ['sometimes', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        $dto    = UpdateClientDTO::fromArray($validated);
        $client = $this->clientService->updateClient($client, $dto);

        return response()->json([
            'message' => 'Client updated successfully.',
            'client'  => $client,
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $tenant = app('tenant');
        $client = AgencyClient::where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        $this->clientService->deleteClient($client);

        return response()->json(['message' => 'Client deleted.']);
    }

    public function stats(): JsonResponse
    {
        $tenant = app('tenant');
        $stats  = $this->clientService->agencyDashboardStats($tenant);

        return response()->json($stats);
    }
}
