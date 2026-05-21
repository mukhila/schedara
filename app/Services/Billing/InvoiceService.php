<?php

namespace App\Services\Billing;

use App\Models\BillingLog;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    // Tax rate as a percentage; configurable via config/billing.php
    private function taxRate(string $currency): float
    {
        return match (strtolower($currency)) {
            'inr'   => (float) config('billing.gst_rate', 18),
            default => (float) config('billing.tax_rate', 0),
        };
    }

    private function taxLabel(string $currency): string
    {
        return match (strtolower($currency)) {
            'inr'   => 'GST',
            'eur'   => 'VAT',
            default => 'Tax',
        };
    }

    /**
     * Create a subscription invoice with auto-generated invoice number + tax.
     */
    public function createForSubscription(
        Tenant       $tenant,
        Subscription $subscription,
        int          $amountCents,
        string       $currency,
        int          $discountCents = 0,
        array        $lineItems    = [],
        array        $meta         = []
    ): Invoice {
        $taxRate    = $this->taxRate($currency);
        $taxable    = max(0, $amountCents - $discountCents);
        $taxCents   = (int) round($taxable * $taxRate / 100);
        $totalCents = $taxable + $taxCents;

        $invoice = Invoice::create([
            'uuid'            => (string) Str::uuid(),
            'tenant_id'       => $tenant->id,
            'subscription_id' => $subscription->id,
            'invoice_number'  => $this->nextInvoiceNumber(),
            'provider'        => $subscription->provider,
            'provider_invoice_id' => $meta['provider_invoice_id'] ?? ('local-' . Str::uuid()),
            'status'          => 'open',
            'amount'          => $amountCents,
            'tax'             => $taxCents,
            'discount'        => $discountCents,
            'total'           => $totalCents,
            'currency'        => strtolower($currency),
            'tax_rate'        => $taxRate > 0 ? $taxRate : null,
            'tax_label'       => $taxRate > 0 ? $this->taxLabel($currency) : null,
            'line_items'      => $lineItems,
            'billing_address' => $meta['billing_address'] ?? null,
            'due_date'        => now()->addDays(7),
        ]);

        BillingLog::record(
            'invoice.created',
            ['invoice_number' => $invoice->invoice_number, 'total' => $totalCents],
            [],
            'success',
            $subscription->provider,
            $tenant->id
        );

        return $invoice;
    }

    /** Mark invoice paid and record the payment timestamp. */
    public function markPaid(Invoice $invoice, ?string $transactionId = null): Invoice
    {
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
            'metadata' => array_merge($invoice->metadata ?? [], array_filter(['transaction_id' => $transactionId])),
        ]);

        return $invoice;
    }

    /** Generate PDF for an invoice. Returns the storage path. */
    public function generatePdf(Invoice $invoice): string
    {
        $invoice->load(['subscription.plan', 'tenant']);

        $path = "invoices/{$invoice->uuid}.pdf";

        $data = [
            'invoice'      => $invoice,
            'tenant'       => $invoice->tenant,
            'subscription' => $invoice->subscription,
            'plan'         => $invoice->subscription?->plan,
        ];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.billing.invoice', $data)->setPaper('a4');
            Storage::put($path, $pdf->output());
        } else {
            Storage::put($path, view('reports.billing.invoice', $data)->render());
        }

        $invoice->update(['invoice_pdf' => $path]);

        return $path;
    }

    /** Generate a sequential invoice number: INV-YYYYMM-XXXX. */
    public function nextInvoiceNumber(): string
    {
        $prefix  = 'INV-' . now()->format('Ym') . '-';
        $lastNum = Invoice::where('invoice_number', 'like', $prefix . '%')
                          ->orderByDesc('invoice_number')
                          ->value('invoice_number');

        $sequence = $lastNum
            ? (int) substr($lastNum, -4) + 1
            : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /** Return paginated invoices for a tenant. */
    public function paginateForTenant(int $tenantId, int $perPage = 20)
    {
        return Invoice::where('tenant_id', $tenantId)
                      ->with('subscription.plan')
                      ->latest()
                      ->paginate($perPage);
    }
}
