<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f5fefe;color:#021b2e;margin:0;padding:0}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e3e9ee}
  .hdr{background:#021b2e;padding:28px 32px;color:#fff;display:flex;justify-content:space-between;align-items:center}
  .brand{font-size:18px;font-weight:800}
  .inv-no{font-size:11px;opacity:.6}
  .body{padding:32px}
  h2{font-size:18px;font-weight:700;margin-bottom:12px}
  .meta-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e3e9ee;font-size:13px}
  .meta-label{color:#718096;font-weight:600}
  .total-row{display:flex;justify-content:space-between;padding:12px 0;font-size:16px;font-weight:800;border-top:2px solid #021b2e;margin-top:8px}
  .status{display:inline-block;padding:4px 12px;border-radius:9999px;font-size:11px;font-weight:700}
  .status-open{background:rgba(253,187,31,.15);color:#a37d0a}
  .status-paid{background:rgba(34,176,126,.1);color:#22B07E}
  .footer{background:#f5fefe;padding:16px 32px;text-align:center;font-size:11px;color:#a0aec0;border-top:1px solid #e3e9ee}
</style></head>
<body>
<div class="wrap">
  <div class="hdr">
    <div class="brand">Schedara</div>
    <div class="inv-no">Invoice #{{ $invoice->invoice_number }}</div>
  </div>
  <div class="body">
    <h2>Invoice for {{ $invoice->client->client_name }}</h2>
    <div class="meta-row"><span class="meta-label">Plan</span><span>{{ $invoice->subscription_plan }}</span></div>
    <div class="meta-row"><span class="meta-label">Amount</span><span>${{ number_format($invoice->amount/100,2) }}</span></div>
    <div class="meta-row"><span class="meta-label">Tax</span><span>${{ number_format($invoice->tax/100,2) }}</span></div>
    <div class="meta-row"><span class="meta-label">Due Date</span><span>{{ $invoice->due_date?->format('M d, Y') }}</span></div>
    <div class="meta-row"><span class="meta-label">Status</span>
      <span class="status {{ $invoice->isPaid() ? 'status-paid' : 'status-open' }}">{{ ucfirst($invoice->payment_status) }}</span>
    </div>
    <div class="total-row"><span>Total</span><span>{{ $invoice->formattedTotal() }}</span></div>
    @if(!empty($invoice->notes))
    <p style="font-size:13px;color:#718096;margin-top:16px">{{ $invoice->notes }}</p>
    @endif
  </div>
  <div class="footer">© {{ date('Y') }} Schedara. Questions? Contact your agency.</div>
</div>
</body>
</html>
