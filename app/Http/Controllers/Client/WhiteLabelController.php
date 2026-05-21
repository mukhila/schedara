<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientWorkspace;
use App\Services\Client\WhiteLabelService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhiteLabelController extends Controller
{
    public function __construct(
        private readonly WhiteLabelService $whiteLabelService,
    ) {}

    public function edit(string $workspaceUuid): View
    {
        $workspace = $this->resolveWorkspace($workspaceUuid);
        $settings  = $this->whiteLabelService->getSettings($workspace);

        return view('backend.agency.white-label.edit', [
            'workspace' => $workspace,
            'settings'  => $settings,
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
