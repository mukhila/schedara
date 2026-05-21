<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeService $stripe): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        try {
            $stripe->handleWebhook($payload, $signature);
        } catch (\RuntimeException $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Webhook error: ' . $e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook exception', ['error' => $e->getMessage()]);
            return response('Internal error', 500);
        }

        return response('OK', 200);
    }
}
