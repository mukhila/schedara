<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('subscriptions')->orderBy('sort_order')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'slug'             => 'nullable|string|max:100|unique:plans,slug',
            'description'      => 'nullable|string',
            'price_monthly'    => 'required|integer|min:0',
            'price_yearly'     => 'nullable|integer|min:0',
            'trial_days'       => 'nullable|integer|min:0',
            'features'         => 'nullable|array',
            'limits'           => 'nullable|array',
            'is_public'        => 'boolean',
            'sort_order'       => 'nullable|integer',
        ]);

        $plan = Plan::create($data);

        AdminActivityLog::record('create', 'plans', "Created plan '{$plan->name}'", $plan);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string',
            'price_monthly' => 'required|integer|min:0',
            'price_yearly'  => 'nullable|integer|min:0',
            'trial_days'    => 'nullable|integer|min:0',
            'features'      => 'nullable|array',
            'limits'        => 'nullable|array',
            'is_public'     => 'boolean',
            'sort_order'    => 'nullable|integer',
        ]);

        $plan->update($data);

        AdminActivityLog::record('update', 'plans', "Updated plan '{$plan->name}'", $plan);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->whereIn('status', ['active', 'trialing'])->exists()) {
            return back()->with('error', 'Cannot delete a plan with active subscriptions.');
        }

        AdminActivityLog::record('delete', 'plans', "Deleted plan '{$plan->name}'", $plan);

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted.');
    }
}
