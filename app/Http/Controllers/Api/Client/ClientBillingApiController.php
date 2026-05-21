<?php

namespace App\Http\Controllers\Api\Client;

use App\DTOs\Client\CreateInvoiceDTO;
use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Models\ClientBilling;
use App\Services\Client\ClientBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientBillingApiController extends Controller
{
    public function __construct(
        private readonly ClientBillingService $billingService,
    ) {}

    public function index(Request $request, string $clientUuid): JsonResponse
    {
        $client   = $this->resolveClient($clientUuid);
        $filters  = $request->only(['status', 'per_page']);
        $invoices = $this->billingService->listInvoices($client, $filters);

        return response()->json($invoices);
    }

    public function store(Request $request, string $clientUuid): JsonResponse
    {
        $client = $this->resolveClient($clientUuid);

        $validated = $request->validate([
            'subscription_plan' => 'required|string|max:100',
            'amount'            => 'required|integer|min:0',
            'tax'               => 'nullable|integer|min:0',
            'currency'          => 'nullable|string|size:3',
            'provider'          => ['nullable', Rule::in(['stripe', 'razorpay', 'paypal', 'manual'])],
            'line_items'        => 'nullable|array',
            'due_date'          => 'nullable|date',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $dto     = CreateInvoiceDTO::fromArray($validated);
        $invoice = $this->billingService->createInvoice($client, $dto);

        return response()->json([
            'message' => 'Invoice created.',
            'invoice' => $invoice,
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $invoice = ClientBilling::where('uuid', $uuid)->firstOrFail();

        return response()->json($invoice);
    }

    public function markPaid(string $uuid): JsonResponse
    {
        $invoice = ClientBilling::where('uuid', $uuid)->firstOrFail();
        $invoice = $this->billingService->markAsPaid($invoice);

        return response()->json([
            'message' => 'Invoice marked as paid.',
            'invoice' => $invoice,
        ]);
    }

    public function voidInvoice(string $uuid): JsonResponse
    {
        $invoice = ClientBilling::where('uuid', $uuid)->firstOrFail();
        $invoice = $this->billingService->voidInvoice($invoice);

        return response()->json([
            'message' => 'Invoice voided.',
            'invoice' => $invoice,
        ]);
    }

    public function payWithStripe(Request $request, string $uuid): JsonResponse
    {
        $invoice = ClientBilling::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $invoice = $this->billingService->processStripePayment(
            $invoice,
            $validated['payment_method_id'],
        );

        return response()->json([
            'message' => 'Payment successful.',
            'invoice' => $invoice,
        ]);
    }

    public function payWithRazorpay(Request $request, string $uuid): JsonResponse
    {
        $invoice = ClientBilling::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'payment_id' => 'required|string',
            'order_id'   => 'required|string',
            'signature'  => 'required|string',
        ]);

        $invoice = $this->billingService->processRazorpayPayment(
            $invoice,
            $validated['payment_id'],
            $validated['order_id'],
            $validated['signature'],
        );

        return response()->json([
            'message' => 'Payment successful.',
            'invoice' => $invoice,
        ]);
    }

    public function revenue(): JsonResponse
    {
        $tenant = app('tenant');
        $stats  = $this->billingService->agencyRevenueStats($tenant);

        return response()->json($stats);
    }

    private function resolveClient(string $uuid): AgencyClient
    {
        $tenant = app('tenant');

        return AgencyClient::where('uuid', $uuid)
            ->where('agency_id', $tenant->id)
            ->firstOrFail();
    }
}
