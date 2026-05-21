<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Services\Client\ClientBillingService;
use App\Services\Client\ClientService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgencyDashboardController extends Controller
{
    public function __construct(
        private readonly ClientService        $clientService,
        private readonly ClientBillingService $billingService,
    ) {}

    public function index(Request $request): View
    {
        $tenant  = app('tenant');
        $filters = $request->only(['status', 'search', 'industry', 'per_page']);

        $clients = $this->clientService->listClients($tenant, array_merge($filters, ['per_page' => 12]));
        $stats   = $this->clientService->agencyDashboardStats($tenant);
        $revenue = $this->billingService->agencyRevenueStats($tenant);

        return view('backend.agency.dashboard', [
            'clients' => $clients,
            'stats'   => array_merge($stats, $revenue),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('backend.agency.clients.create');
    }

    public function show(string $uuid): View
    {
        $tenant = app('tenant');
        $client = AgencyClient::with(['workspace.whiteLabelSettings', 'onboardingSteps', 'billing'])
            ->where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        return view('backend.agency.clients.show', [
            'client'   => $client,
            'progress' => $client->onboardingProgress(),
        ]);
    }

    public function onboarding(string $uuid): View
    {
        $tenant = app('tenant');
        $client = AgencyClient::with('onboardingSteps')
            ->where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        return view('backend.agency.clients.onboarding', [
            'client'   => $client,
            'steps'    => $client->onboardingSteps,
            'progress' => $client->onboardingProgress(),
        ]);
    }
}
