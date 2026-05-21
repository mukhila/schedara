<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class BillingPlanApiController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::active()
                     ->with('features')
                     ->orderBy('sort_order')
                     ->get()
                     ->map(fn (Plan $p) => [
                         'id'             => $p->id,
                         'name'           => $p->name,
                         'slug'           => $p->slug,
                         'description'    => $p->description,
                         'price_monthly'  => $p->price_monthly,
                         'price_yearly'   => $p->price_yearly,
                         'currency'       => $p->currency,
                         'trial_days'     => $p->trial_days,
                         'is_popular'     => $p->is_popular,
                         'features'       => $p->features,
                         'limits'         => $p->limits,
                         'yearly_discount' => $p->yearlyDiscount(),
                     ]);

        return response()->json(['data' => $plans]);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['data' => $plan->load('features')]);
    }
}
