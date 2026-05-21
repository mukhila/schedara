<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayService $razorpay): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature', '');

        try {
            $razorpay->handleWebhook($payload, $signature);
        } catch (\RuntimeException $e) {
            Log::error('Razorpay webhook error: ' . $e->getMessage());
            return response('Webhook error: ' . $e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('Razorpay webhook exception', ['error' => $e->getMessage()]);
            return response('Internal error', 500);
        }

        return response('OK', 200);
    }
}
