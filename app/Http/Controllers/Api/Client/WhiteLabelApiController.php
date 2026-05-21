<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientWorkspace;
use App\Services\Client\WhiteLabelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhiteLabelApiController extends Controller
{
    public function __construct(
        private readonly WhiteLabelService $whiteLabelService,
    ) {}

    public function show(string $workspaceUuid): JsonResponse
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);
        $settings  = $this->whiteLabelService->getSettings($workspace);

        return response()->json($settings);
    }

    public function update(Request $request, string $workspaceUuid): JsonResponse
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);

        $validated = $request->validate([
            'brand_name'         => 'nullable|string|max:255',
            'primary_color'      => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color'    => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'custom_domain'      => 'nullable|string|max:255',
            'support_email'      => 'nullable|email|max:255',
            'support_url'        => 'nullable|url|max:255',
            'hide_saas_branding' => 'boolean',
            'social_links'       => 'nullable|array',
            'email_settings'     => 'nullable|array',
        ]);

        $settings = $this->whiteLabelService->updateSettings($workspace, $validated);

        return response()->json([
            'message'  => 'White-label settings updated.',
            'settings' => $settings,
        ]);
    }

    public function uploadLogo(Request $request, string $workspaceUuid): JsonResponse
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);

        $request->validate(['logo' => 'required|image|max:2048']);

        $path = $request->file('logo')->store("white-label/{$workspace->id}", 'public');

        $settings = $this->whiteLabelService->updateSettings($workspace, ['logo' => $path]);

        return response()->json([
            'message' => 'Logo uploaded.',
            'logo'    => $path,
        ]);
    }

    public function verifyDomain(string $workspaceUuid): JsonResponse
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);
        $settings  = $this->whiteLabelService->getSettings($workspace);

        if (!$settings) {
            return response()->json(['message' => 'No white-label settings found.'], 404);
        }

        $verified = $this->whiteLabelService->verifyDomain($settings);

        return response()->json([
            'verified' => $verified,
            'message'  => $verified ? 'Domain verified.' : 'DNS record not found yet.',
        ]);
    }

    private function resolveWorkspace(string $uuid): ClientWorkspace
    {
        $tenant = app('tenant');

        return ClientWorkspace::whereHas('client', fn ($q) => $q->where('agency_id', $tenant->id))
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
