<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Billing\BillingManager;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionApiController extends Controller
{
    public function __construct(
        private readonly BillingManager     $billing,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /** GET /api/billing/subscription — current subscription */
    public function current(): JsonResponse
    {
        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->with('plan')->latest()->first();

        return response()->json(['data' => $subscription]);
    }

    /** POST /api/billing/subscribe */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id'  => 'required|exists:plans,id',
            'interval' => 'required|in:monthly,yearly',
            'provider' => 'required|in:stripe,razorpay,paypal,free',
        ]);

        $tenant = app('current.tenant');
        $plan   = Plan::findOrFail($validated['plan_id']);

        if ($validated['provider'] === 'free') {
            $sub = $this->subscriptionService->activateFree($tenant, $plan);

            return response()->json(['data' => $sub, 'message' => 'Subscribed successfully.']);
        }

        $result = $this->billing->initiateCheckout($tenant, $plan, $validated['interval'], $validated['provider']);

        return response()->json(['data' => $result]);
    }

    /** POST /api/billing/upgrade */
    public function upgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id'  => 'required|exists:plans,id',
            'interval' => 'required|in:monthly,yearly',
        ]);

        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->whereIn('status', ['active', 'trialing'])->latest()->firstOrFail();
        $newPlan      = Plan::findOrFail($validated['plan_id']);

        $url = $this->subscriptionService->upgrade($tenant, $subscription, $newPlan, $validated['interval']);

        return response()->json(['data' => ['redirect_url' => $url]]);
    }

    /** POST /api/billing/downgrade — same as upgrade, handled by gateway at period end */
    public function downgrade(Request $request): JsonResponse
    {
        return $this->upgrade($request);
    }

    /** POST /api/billing/cancel */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'immediately' => 'boolean',
        ]);

        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->whereIn('status', ['active', 'trialing', 'past_due'])->latest()->firstOrFail();

        $updated = $this->subscriptionService->cancel($subscription, $validated['immediately'] ?? false);

        return response()->json(['data' => $updated, 'message' => 'Subscription cancellation scheduled.']);
    }

    /** POST /api/billing/pause */
    public function pause(): JsonResponse
    {
        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->where('status', 'active')->latest()->firstOrFail();

        $updated = $this->subscriptionService->pause($subscription);

        return response()->json(['data' => $updated, 'message' => 'Subscription paused.']);
    }

    /** POST /api/billing/resume */
    public function resume(): JsonResponse
    {
        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->where('status', 'paused')->latest()->firstOrFail();

        $updated = $this->subscriptionService->resume($subscription);

        return response()->json(['data' => $updated, 'message' => 'Subscription resumed.']);
    }
}
