<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use App\Models\ClientWorkspace;
use App\Services\Client\WhiteLabelService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    public function __construct(
        private readonly WhiteLabelService $whiteLabelService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $memberships = ClientUser::with('workspace.client')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        return view('backend.portal.dashboard', [
            'memberships' => $memberships,
        ]);
    }

    public function workspace(Request $request, string $workspaceUuid): View
    {
        $workspace = ClientWorkspace::where('uuid', $workspaceUuid)
            ->with(['client', 'reports', 'whiteLabelSettings'])
            ->firstOrFail();

        $membership = ClientUser::where('client_workspace_id', $workspace->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();

        $settings = $this->whiteLabelService->getSettings($workspace);

        return view('backend.portal.workspace', [
            'workspace' => $workspace,
            'client'    => $workspace->client,
            'reports'   => $workspace->reports()->limit(10)->get(),
            'settings'  => $settings,
            'role'      => $membership->role,
        ]);
    }
}
