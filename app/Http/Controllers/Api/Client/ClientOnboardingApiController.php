<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Models\ClientOnboarding;
use App\Services\Client\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientOnboardingApiController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    public function show(string $uuid): JsonResponse
    {
        $tenant = app('tenant');
        $client = AgencyClient::where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->with('onboardingSteps')
            ->firstOrFail();

        return response()->json([
            'client'   => $client,
            'steps'    => $client->onboardingSteps,
            'progress' => $client->onboardingProgress(),
        ]);
    }

    public function completeStep(Request $request, string $uuid): JsonResponse
    {
        $tenant = app('tenant');
        $client = AgencyClient::where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        $validated = $request->validate([
            'step'      => ['required', Rule::in(['profile', 'branding', 'social', 'team', 'content', 'billing'])],
            'step_data' => 'nullable|array',
        ]);

        $success = $this->clientService->completeOnboardingStep(
            $client,
            $validated['step'],
            $validated['step_data'] ?? [],
        );

        return response()->json([
            'message'  => $success ? 'Step completed.' : 'Step not found.',
            'progress' => $client->fresh()->onboardingProgress(),
        ]);
    }

    public function skipStep(Request $request, string $uuid): JsonResponse
    {
        $tenant = app('tenant');
        $client = AgencyClient::where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        $validated = $request->validate([
            'step' => ['required', Rule::in(['social', 'team', 'content', 'billing'])],
        ]);

        ClientOnboarding::where('agency_client_id', $client->id)
            ->where('onboarding_step', $validated['step'])
            ->update(['status' => 'skipped']);

        return response()->json(['message' => 'Step skipped.']);
    }
}
