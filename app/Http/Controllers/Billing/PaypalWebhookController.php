<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaypalWebhookController extends Controller
{
    public function __invoke(Request $request, PaypalService $paypal): Response
    {
        $payload = $request->getContent();
        $headers = array_change_key_case($request->headers->all(), CASE_UPPER);
        // Flatten header arrays
        $headers = array_map(fn ($v) => is_array($v) ? $v[0] : $v, $headers);

        try {
            $paypal->handleWebhook($payload, $headers);
        } catch (\RuntimeException $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage());
            return response('Webhook error: ' . $e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('PayPal webhook exception', ['error' => $e->getMessage()]);
            return response('Internal error', 500);
        }

        return response('OK', 200);
    }
}
