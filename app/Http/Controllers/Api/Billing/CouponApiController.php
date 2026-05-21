<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    public function __construct(private readonly CouponService $couponService) {}

    /** POST /api/billing/apply-coupon */
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code'   => 'required|string|max:50',
            'plan_slug'     => 'required|string',
            'billing_cycle' => 'required|in:monthly,yearly',
            'amount'        => 'required|integer|min:0',
        ]);

        $tenant = app('current.tenant');

        try {
            $coupon  = $this->couponService->validate(
                $validated['coupon_code'],
                $tenant,
                $validated['plan_slug'],
                $validated['billing_cycle']
            );

            $pricing = $this->couponService->calculatePrice($validated['amount'], $coupon);
            $extra   = $this->couponService->extraTrialDays($coupon);

            return response()->json([
                'data' => [
                    'coupon'         => $coupon->only(['name', 'discount_type', 'discount_value']),
                    'original'       => $pricing['original'],
                    'discount'       => $pricing['discount'],
                    'final'          => $pricing['final'],
                    'extra_trial_days' => $extra,
                    'label'          => $coupon->formattedDiscount(),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /** GET /api/billing/validate-coupon?code=XXX */
    public function validate(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $coupon = \App\Models\Coupon::where('coupon_code', strtoupper($request->code))->first();

        if (! $coupon || ! $coupon->isValid()) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired coupon.']);
        }

        return response()->json([
            'valid'  => true,
            'coupon' => [
                'name'    => $coupon->name,
                'type'    => $coupon->discount_type,
                'label'   => $coupon->formattedDiscount(),
            ],
        ]);
    }
}
