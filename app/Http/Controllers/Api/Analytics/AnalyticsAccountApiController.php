<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Services\Analytics\AnalyticsAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsAccountApiController extends Controller
{
    public function __construct(private AnalyticsAccountService $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenant   = app('current.tenant');
        $active   = $request->boolean('active', true);
        $accounts = $this->service->listForTenant($tenant->id, $active);

        return response()->json(['data' => $accounts]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['social_account_id' => 'required|integer']);

        $tenant        = app('current.tenant');
        $socialAccount = SocialAccount::where('tenant_id', $tenant->id)
            ->findOrFail($request->input('social_account_id'));

        $account = $this->service->register($tenant->id, $socialAccount);

        return response()->json(['data' => $account], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $tenant  = app('current.tenant');
        $account = \App\Models\AnalyticsAccount::forTenant($tenant->id)
            ->where('uuid', $uuid)
            ->with('socialAccount')
            ->firstOrFail();

        return response()->json(['data' => $account]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $tenant  = app('current.tenant');
        $account = \App\Models\AnalyticsAccount::forTenant($tenant->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $this->service->deactivate($account);

        return response()->json(['message' => 'Account deactivated.']);
    }

    public function metrics(Request $request, string $uuid): JsonResponse
    {
        $tenant  = app('current.tenant');
        $account = \App\Models\AnalyticsAccount::forTenant($tenant->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $timeSeries = $this->service->metricsTimeSeries(
            $tenant->id,
            $from,
            $to,
            $account->platform
        );

        return response()->json(['data' => $timeSeries]);
    }
}
