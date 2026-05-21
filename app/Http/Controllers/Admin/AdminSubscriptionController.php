<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Subscription;
use App\Services\Billing\RevenueDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubscriptionController extends Controller
{
    public function __construct(private RevenueDashboardService $revenue) {}

    public function index(Request $request): View
    {
        $query = Subscription::with(['tenant', 'plan'])
            ->when($request->search, fn ($q) => $q->whereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$request->search}%")))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->plan, fn ($q) => $q->where('plan_id', $request->plan));

        $subscriptions = $query->latest()->paginate(25)->withQueryString();
        $summary       = $this->revenue->getSummary();

        return view('admin.subscriptions.index', compact('subscriptions', 'summary'));
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['tenant', 'plan']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $subscription->update(['status' => 'cancelled', 'cancel_at' => now()]);

        AdminActivityLog::record('cancel', 'subscriptions', "Cancelled subscription for tenant {$subscription->tenant?->name}", $subscription);

        return back()->with('success', 'Subscription cancelled.');
    }

    public function extendTrial(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate(['days' => 'required|integer|min:1|max:90']);

        $base = $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture()
            ? $subscription->trial_ends_at
            : now();
        $subscription->update(['trial_ends_at' => $base->addDays($request->days)]);

        AdminActivityLog::record('extend_trial', 'subscriptions', "Extended trial by {$request->days} days for {$subscription->tenant?->name}", $subscription);

        return back()->with('success', "Trial extended by {$request->days} days.");
    }
}
