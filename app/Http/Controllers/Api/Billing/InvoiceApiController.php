<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Billing\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceApiController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    /** GET /api/billing/invoices */
    public function index(Request $request): JsonResponse
    {
        $tenant   = app('current.tenant');
        $invoices = $this->invoiceService->paginateForTenant($tenant->id);

        return response()->json($invoices);
    }

    /** GET /api/billing/invoices/{uuid} */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorizeInvoice($invoice);

        return response()->json(['data' => $invoice->load('subscription.plan')]);
    }

    /** GET /api/billing/invoices/{uuid}/download */
    public function download(Invoice $invoice): StreamedResponse|\Illuminate\Http\Response
    {
        $this->authorizeInvoice($invoice);

        if (! $invoice->invoice_pdf || ! Storage::exists($invoice->invoice_pdf)) {
            $this->invoiceService->generatePdf($invoice->fresh());
            $invoice->refresh();
        }

        return Storage::download($invoice->invoice_pdf, "invoice-{$invoice->invoice_number}.pdf");
    }

    private function authorizeInvoice(Invoice $invoice): void
    {
        if ($invoice->tenant_id !== app('current.tenant')->id) {
            abort(403);
        }
    }
}
