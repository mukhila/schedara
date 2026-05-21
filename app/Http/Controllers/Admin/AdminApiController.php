<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiIntegration;
use App\Services\Admin\ApiIntegrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminApiController extends Controller
{
    public function __construct(private ApiIntegrationService $apis) {}

    public function index(): View
    {
        $integrations  = $this->apis->all();
        $totalCost     = $this->apis->getTotalMonthlyCost();

        return view('admin.api.index', compact('integrations', 'totalCost'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider_name'      => 'required|string|max:100|unique:api_integrations',
            'display_name'       => 'required|string|max:150',
            'api_key'            => 'nullable|string',
            'api_secret'         => 'nullable|string',
            'environment'        => 'required|in:production,sandbox,test',
            'usage_limit'        => 'nullable|integer|min:0',
            'monthly_cost_cents' => 'nullable|integer|min:0',
        ]);

        $this->apis->create($data);

        return redirect()->route('admin.api.index')->with('success', 'Integration added.');
    }

    public function update(Request $request, ApiIntegration $apiIntegration): RedirectResponse
    {
        $data = $request->validate([
            'display_name'       => 'required|string|max:150',
            'api_key'            => 'nullable|string',
            'api_secret'         => 'nullable|string',
            'environment'        => 'required|in:production,sandbox,test',
            'status'             => 'required|in:active,inactive,error',
            'usage_limit'        => 'nullable|integer|min:0',
            'monthly_cost_cents' => 'nullable|integer|min:0',
        ]);

        // Don't overwrite encrypted keys if left blank
        if (empty($data['api_key']))    unset($data['api_key']);
        if (empty($data['api_secret'])) unset($data['api_secret']);

        $this->apis->update($apiIntegration, $data);

        return redirect()->route('admin.api.index')->with('success', 'Integration updated.');
    }

    public function healthCheck(ApiIntegration $apiIntegration): RedirectResponse
    {
        $result = $this->apis->healthCheck($apiIntegration);

        $msg = $result['status'] === 'active'
            ? "{$apiIntegration->display_name} is healthy."
            : "Health check failed: {$result['error']}";

        return back()->with($result['status'] === 'active' ? 'success' : 'error', $msg);
    }

    public function destroy(ApiIntegration $apiIntegration): RedirectResponse
    {
        $this->apis->delete($apiIntegration);

        return redirect()->route('admin.api.index')->with('success', 'Integration removed.');
    }
}
