<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Services\Client\ClientBillingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientBillingController extends Controller
{
    public function __construct(
        private readonly ClientBillingService $billingService,
    ) {}

    public function index(string $clientUuid): View
    {
        $tenant  = app('tenant');
        $client  = AgencyClient::where('uuid', $clientUuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        $invoices = $this->billingService->listInvoices($client);
        $revenue  = $this->billingService->agencyRevenueStats($tenant);

        return view('backend.agency.billing.index', [
            'client'   => $client,
            'invoices' => $invoices,
            'revenue'  => $revenue,
        ]);
    }

    public function create(string $clientUuid): View
    {
        $tenant = app('tenant');
        $client = AgencyClient::where('uuid', $clientUuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();

        return view('backend.agency.billing.create', [
            'client' => $client,
        ]);
    }
}
