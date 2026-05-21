<?php

namespace App\Http\Controllers\Collaboration;

use App\Http\Controllers\Controller;
use App\Models\PostApproval;
use App\Services\Collaboration\PostApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalCenterController extends Controller
{
    public function __construct(private readonly PostApprovalService $approvals) {}

    public function index(Request $request): View
    {
        $tenant  = app('current.tenant');
        $tab     = $request->get('tab', 'pending');
        $filters = ['status' => $tab === 'all' ? null : 'pending', 'per_page' => 20];

        $approvals = $this->approvals->allForTenant($tenant->id, $filters);
        $stats     = $this->approvals->stats($tenant->id);

        return view('backend.collaboration.approvals.index', compact('approvals', 'stats', 'tab'));
    }

    public function show(string $uuid): View
    {
        $tenant   = app('current.tenant');
        $approval = PostApproval::where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->with(['post', 'requester', 'reviewer'])
            ->firstOrFail();

        return view('backend.collaboration.approvals.show', compact('approval'));
    }

    public function approve(Request $request, string $uuid): RedirectResponse
    {
        $tenant   = app('current.tenant');
        $approval = PostApproval::where('tenant_id', $tenant->id)->where('uuid', $uuid)->where('status', 'pending')->firstOrFail();

        $data = $request->validate(['comment' => 'nullable|string|max:1000']);
        $this->approvals->approve($approval, $request->user()->id, $data['comment'] ?? null);

        return back()->with('success', 'Post approved.');
    }

    public function reject(Request $request, string $uuid): RedirectResponse
    {
        $tenant   = app('current.tenant');
        $approval = PostApproval::where('tenant_id', $tenant->id)->where('uuid', $uuid)->where('status', 'pending')->firstOrFail();

        $data = $request->validate(['reason' => 'required|string|max:1000']);
        $this->approvals->reject($approval, $request->user()->id, $data['reason']);

        return back()->with('success', 'Post rejected.');
    }
}
