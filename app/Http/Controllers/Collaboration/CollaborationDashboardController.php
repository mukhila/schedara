<?php

namespace App\Http\Controllers\Collaboration;

use App\Http\Controllers\Controller;
use App\Models\CollaborationTask;
use App\Models\PostApproval;
use App\Models\TenantUser;
use App\Services\Collaboration\ActivityLogService;
use App\Services\Collaboration\PostApprovalService;
use App\Services\Collaboration\TaskService;
use Illuminate\View\View;

class CollaborationDashboardController extends Controller
{
    public function __construct(
        private readonly TaskService        $tasks,
        private readonly PostApprovalService $approvals,
        private readonly ActivityLogService  $activityLogs,
    ) {}

    public function __invoke(): View
    {
        $tenant = app('current.tenant');
        $user   = auth()->user();

        $taskStats     = $this->tasks->stats($tenant->id);
        $approvalStats = $this->approvals->stats($tenant->id);

        $myTasks = CollaborationTask::where('tenant_id', $tenant->id)
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'rejected'])
            ->with('assignedBy')
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->limit(5)
            ->get();

        $pendingApprovals = PostApproval::where('tenant_id', $tenant->id)
            ->pending()
            ->with(['post', 'requester'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentActivity = $this->activityLogs->recentForTenant($tenant->id, 15);

        $memberCount = TenantUser::where('tenant_id', $tenant->id)
            ->whereNotNull('joined_at')
            ->count();

        return view('backend.collaboration.dashboard', compact(
            'taskStats', 'approvalStats', 'myTasks',
            'pendingApprovals', 'recentActivity', 'memberCount',
        ));
    }
}
