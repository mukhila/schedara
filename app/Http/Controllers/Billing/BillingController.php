<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\Billing\BillingManager;
use App\Services\Billing\CouponService;
use App\Services\Billing\RevenueDashboardService;
use App\Services\Billing\SubscriptionService;
use App\Services\Billing\UsageLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private readonly BillingManager          $billing,
        private readonly SubscriptionService     $subscriptionService,
        private readonly UsageLimitService       $usageLimit,
        private readonly RevenueDashboardService $revenue,
        private readonly CouponService           $couponService,
    ) {}

    public function index(): View
    {
        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->with('plan')->latest()->first();
        $invoices     = $tenant->invoices()->with('subscription.plan')->latest()->take(20)->get();
        $plan         = $tenant->plan;
        $usage        = $this->usageLimit->allForTenant($tenant->id);

        return view('backend.billing.index', compact('tenant', 'subscription', 'invoices', 'plan', 'usage'));
    }

    public function plans(): View
    {
        $tenant        = app('current.tenant');
        $plans         = Plan::active()->orderBy('price_monthly')->get();
        $subscription  = $tenant->subscription;
        $currentPlanId = $subscription?->plan_id ?? $tenant->plan_id;

        return view('backend.billing.plans', compact('tenant', 'plans', 'subscription', 'currentPlanId'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id'  => 'required|exists:plans,id',
            'interval' => 'required|in:monthly,yearly',
            'provider' => 'required|in:stripe,razorpay',
        ]);

        $tenant = app('current.tenant');
        $plan   = Plan::findOrFail($validated['plan_id']);

        try {
            $result = $this->billing->initiateCheckout($tenant, $plan, $validated['interval'], $validated['provider']);

            return redirect()->away($result['url']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function portal(): RedirectResponse
    {
        $tenant = app('current.tenant');

        try {
            $url = $this->billing->stripe()->createPortalSession($tenant);
            return redirect()->away($url);
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not open billing portal. Please try again.');
        }
    }

    public function usage(): View
    {
        $tenant = app('current.tenant');
        $usage  = $this->usageLimit->allForTenant($tenant->id);
        $plan   = $tenant->plan;

        return view('backend.billing.usage', compact('tenant', 'plan', 'usage'));
    }

    public function invoices(): View
    {
        $tenant   = app('current.tenant');
        $invoices = $tenant->invoices()->with('subscription.plan')->latest()->paginate(20);

        return view('backend.billing.invoices', compact('tenant', 'invoices'));
    }

    public function cancel(Request $request): RedirectResponse
    {
        $immediately  = $request->boolean('immediately', false);
        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->whereIn('status', ['active', 'trialing', 'past_due'])->latest()->first();

        if (! $subscription) {
            return back()->with('error', 'No active subscription found.');
        }

        try {
            $this->subscriptionService->cancel($subscription, $immediately);
            $msg = $immediately ? 'Subscription cancelled immediately.' : 'Subscription will cancel at end of billing period.';

            return redirect()->route('billing.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not cancel subscription: ' . $e->getMessage());
        }
    }

    public function pause(): RedirectResponse
    {
        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->where('status', 'active')->latest()->first();

        if (! $subscription) {
            return back()->with('error', 'No active subscription found.');
        }

        $this->subscriptionService->pause($subscription);

        return redirect()->route('billing.index')->with('success', 'Subscription paused.');
    }

    public function resume(): RedirectResponse
    {
        $tenant       = app('current.tenant');
        $subscription = $tenant->subscription()->where('status', 'paused')->latest()->first();

        if (! $subscription) {
            return back()->with('error', 'No paused subscription found.');
        }

        $this->subscriptionService->resume($subscription);

        return redirect()->route('billing.index')->with('success', 'Subscription resumed.');
    }

    public function paypalCallback(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|string',
            'plan_id'         => 'required|exists:plans,id',
            'interval'        => 'required|in:monthly,yearly',
        ]);

        $tenant = app('current.tenant');
        $plan   = Plan::findOrFail($validated['plan_id']);

        try {
            $this->billing->paypal()->activateSubscription(
                $tenant,
                $plan,
                $validated['subscription_id'],
                $validated['interval']
            );

            return redirect()->route('billing.index')->with('success', 'PayPal subscription activated!');
        } catch (\Throwable $e) {
            return redirect()->route('billing.plans')->with('error', 'PayPal activation failed: ' . $e->getMessage());
        }
    }

    public function revenue(): View
    {
        Gate::authorize('super-admin');

        $summary        = $this->revenue->getSummary();
        $revenueByMonth = $this->revenue->getRevenueByMonth(12);
        $byPlan         = $this->revenue->getSubscriptionsByPlan();

        return view('backend.billing.revenue', compact('summary', 'revenueByMonth', 'byPlan'));
    }

    public function razorpayCallback(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'razorpay_payment_id'    => 'required|string',
            'razorpay_subscription_id' => 'required|string',
            'razorpay_signature'     => 'required|string',
            'plan_id'                => 'required|exists:plans,id',
            'interval'               => 'required|in:monthly,yearly',
        ]);

        $valid = $this->billing->razorpay()->verifyPayment(
            $validated['razorpay_subscription_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (! $valid) {
            return redirect()->route('billing.plans')->with('error', 'Payment verification failed.');
        }

        $tenant = app('current.tenant');
        $plan   = Plan::findOrFail($validated['plan_id']);

        $this->billing->razorpay()->activateSubscription(
            $tenant,
            $plan,
            $validated['razorpay_subscription_id'],
            $validated['interval']
        );

        return redirect()->route('billing.index')->with('success', 'Subscription activated successfully!');
    }
}
