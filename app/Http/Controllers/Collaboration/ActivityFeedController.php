<?php

namespace App\Http\Controllers\Collaboration;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use App\Services\Collaboration\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityFeedController extends Controller
{
    public function __construct(private readonly ActivityLogService $service) {}

    public function __invoke(Request $request): View
    {
        $tenant  = app('current.tenant');
        $filters = $request->only(['user_id', 'module', 'action', 'from', 'to', 'per_page']);

        $logs    = $this->service->forTenant($tenant->id, $filters);
        $modules = $this->service->availableModules($tenant->id);

        $members = TenantUser::where('tenant_id', $tenant->id)
            ->whereNotNull('joined_at')
            ->with('user')
            ->get();

        return view('backend.collaboration.activity.index', compact('logs', 'modules', 'members', 'filters'));
    }
}
