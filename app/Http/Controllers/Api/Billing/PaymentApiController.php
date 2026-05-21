<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Jobs\Billing\RetryFailedPaymentJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    /** GET /api/billing/payments */
    public function index(Request $request): JsonResponse
    {
        $tenant   = app('current.tenant');
        $payments = Payment::where('tenant_id', $tenant->id)
                           ->with('invoice')
                           ->latest()
                           ->paginate($request->integer('per_page', 20));

        return response()->json($payments);
    }

    /** POST /api/billing/payments/{uuid}/retry */
    public function retry(Payment $payment): JsonResponse
    {
        if ($payment->tenant_id !== app('current.tenant')->id) {
            abort(403);
        }

        if (! $payment->isFailed()) {
            return response()->json(['message' => 'Only failed payments can be retried.'], 422);
        }

        $sub = Subscription::where('tenant_id', $payment->tenant_id)
                           ->whereIn('status', ['past_due', 'active'])
                           ->latest()
                           ->first();

        if ($sub) {
            RetryFailedPaymentJob::dispatch($sub->id);
        }

        return response()->json(['message' => 'Payment retry queued.']);
    }
}
