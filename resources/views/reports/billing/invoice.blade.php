<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice {{ $invoice->invoice_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1a1a2e; line-height: 1.5; }
  .page { padding: 48px; background: #fff; }
  .header { display: table; width: 100%; margin-bottom: 36px; }
  .header-left { display: table-cell; width: 60%; vertical-align: top; }
  .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
  .brand-name { font-size: 22px; font-weight: 800; color: #021b2e; letter-spacing: -0.03em; }
  .brand-sub { font-size: 11px; color: rgba(2,27,46,0.4); margin-top: 2px; }
  .invoice-label { font-size: 24px; font-weight: 800; color: #021b2e; letter-spacing: -0.04em; }
  .invoice-number { font-size: 13px; color: rgba(2,27,46,0.5); margin-top: 4px; font-family: monospace; }
  .divider { border: none; border-top: 2px solid rgba(2,27,46,0.08); margin: 24px 0; }
  .meta-row { display: table; width: 100%; margin-bottom: 28px; }
  .meta-block { display: table-cell; width: 50%; vertical-align: top; }
  .meta-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(2,27,46,0.4); margin-bottom: 4px; }
  .meta-value { font-size: 12px; font-weight: 600; color: #021b2e; }
  .meta-sub { font-size: 11px; color: rgba(2,27,46,0.5); }
  table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  table.items th { text-align: left; padding: 8px 10px; background: rgba(2,27,46,0.04); font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(2,27,46,0.5); }
  table.items td { padding: 10px; border-bottom: 1px solid rgba(2,27,46,0.06); font-size: 12px; }
  table.items th:last-child, table.items td:last-child { text-align: right; }
  .totals { width: 240px; margin-left: auto; }
  .totals-row { display: table; width: 100%; padding: 5px 0; }
  .totals-label { display: table-cell; font-size: 11px; color: rgba(2,27,46,0.5); }
  .totals-value { display: table-cell; text-align: right; font-size: 11px; font-weight: 600; color: #021b2e; }
  .total-final { border-top: 2px solid rgba(2,27,46,0.12); margin-top: 6px; padding-top: 6px; }
  .total-final .totals-label { font-size: 14px; font-weight: 800; color: #021b2e; }
  .total-final .totals-value { font-size: 16px; font-weight: 800; color: #021b2e; }
  .badge-paid { background: rgba(16,185,129,0.12); color: #059669; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; display: inline-block; }
  .footer { margin-top: 48px; font-size: 10px; color: rgba(2,27,46,0.35); text-align: center; border-top: 1px solid rgba(2,27,46,0.06); padding-top: 16px; }
</style>
</head>
<body>
<div class="page">

  {{-- Header --}}
  <div class="header">
    <div class="header-left">
      <div class="brand-name">{{ config('app.name', 'Schedara') }}</div>
      <div class="brand-sub">Social Media Management Platform</div>
    </div>
    <div class="header-right">
      <div class="invoice-label">INVOICE</div>
      <div class="invoice-number">{{ $invoice->invoice_number }}</div>
      @if($invoice->isPaid())
        <div style="margin-top:6px"><span class="badge-paid">PAID</span></div>
      @endif
    </div>
  </div>

  <hr class="divider">

  {{-- Billing Details --}}
  <div class="meta-row">
    <div class="meta-block">
      <div class="meta-label">Billed To</div>
      <div class="meta-value">{{ $tenant->name }}</div>
      @if($invoice->billing_address)
        <div class="meta-sub">{{ $invoice->billing_address['line1'] ?? '' }}</div>
        <div class="meta-sub">{{ $invoice->billing_address['city'] ?? '' }}{{ isset($invoice->billing_address['state']) ? ', '.$invoice->billing_address['state'] : '' }}</div>
        <div class="meta-sub">{{ $invoice->billing_address['country'] ?? '' }}</div>
        @if(isset($invoice->billing_address['tax_id']))
          <div class="meta-sub">GST: {{ $invoice->billing_address['tax_id'] }}</div>
        @endif
      @endif
    </div>
    <div class="meta-block" style="text-align:right">
      <div class="meta-label">Invoice Details</div>
      <div class="meta-value">Invoice #{{ $invoice->invoice_number }}</div>
      <div class="meta-sub">Date: {{ $invoice->created_at->format('M j, Y') }}</div>
      @if($invoice->due_date)
        <div class="meta-sub">Due: {{ $invoice->due_date->format('M j, Y') }}</div>
      @endif
      @if($invoice->paid_at)
        <div class="meta-sub">Paid: {{ $invoice->paid_at->format('M j, Y') }}</div>
      @endif
    </div>
  </div>

  {{-- Line Items --}}
  <table class="items">
    <thead>
      <tr>
        <th style="width:50%">Description</th>
        <th style="width:15%">Period</th>
        <th style="width:15%">Qty</th>
        <th style="width:20%">Amount</th>
      </tr>
    </thead>
    <tbody>
      @php
        $sym = match(strtolower($invoice->currency ?? 'usd')) { 'inr' => '₹', 'eur' => '€', 'gbp' => '£', default => '$' };
        $lineItems = $invoice->line_items ?? [];
        if (empty($lineItems) && $plan) {
          $lineItems = [[
            'description' => $plan->name . ' Plan (' . ucfirst($invoice->subscription?->interval ?? 'monthly') . ')',
            'period'      => '',
            'qty'         => 1,
            'amount'      => $invoice->amount,
          ]];
        }
      @endphp
      @foreach($lineItems as $item)
        <tr>
          <td>{{ $item['description'] ?? '' }}</td>
          <td style="color:rgba(2,27,46,0.5)">{{ $item['period'] ?? '' }}</td>
          <td>{{ $item['qty'] ?? 1 }}</td>
          <td>{{ $sym }}{{ number_format(($item['amount'] ?? 0) / 100, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Totals --}}
  <div class="totals">
    <div class="totals-row">
      <span class="totals-label">Subtotal</span>
      <span class="totals-value">{{ $sym }}{{ number_format($invoice->amount / 100, 2) }}</span>
    </div>
    @if($invoice->discount > 0)
      <div class="totals-row">
        <span class="totals-label">Discount</span>
        <span class="totals-value" style="color:#059669">−{{ $sym }}{{ number_format($invoice->discount / 100, 2) }}</span>
      </div>
    @endif
    @if($invoice->tax > 0)
      <div class="totals-row">
        <span class="totals-label">{{ $invoice->tax_label ?? 'Tax' }} ({{ $invoice->tax_rate }}%)</span>
        <span class="totals-value">{{ $sym }}{{ number_format($invoice->tax / 100, 2) }}</span>
      </div>
    @endif
    <div class="totals-row total-final">
      <span class="totals-label">Total</span>
      <span class="totals-value">{{ $sym }}{{ number_format(($invoice->total ?: $invoice->amount) / 100, 2) }}</span>
    </div>
  </div>

  @if($invoice->notes)
    <div style="margin-top:24px;padding:12px 16px;background:rgba(2,27,46,.03);border-radius:8px;font-size:11px;color:rgba(2,27,46,.5)">
      <strong>Notes:</strong> {{ $invoice->notes }}
    </div>
  @endif

  <div class="footer">
    Thank you for your business. For questions about this invoice, contact {{ config('mail.from.address') }}
  </div>

</div>
</body>
</html>
