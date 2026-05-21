<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->invoice_number }}</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; padding: 32px 16px; color: #021b2e; }
  .card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid rgba(2,27,46,.1); }
  .card-header { padding: 28px 32px; background: #021b2e; }
  .card-header .brand { font-size: 20px; font-weight: 800; color: #fff; letter-spacing: -0.03em; }
  .card-body { padding: 32px; }
  h2 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
  p { font-size: 14px; line-height: 1.7; color: rgba(2,27,46,.65); margin-bottom: 16px; }
  .amount-box { background: rgba(101,161,216,.06); border: 1px solid rgba(101,161,216,.2); border-radius: 12px; padding: 20px 24px; margin: 24px 0; }
  .amount-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: rgba(2,27,46,.4); margin-bottom: 4px; }
  .amount-value { font-size: 28px; font-weight: 800; color: #021b2e; letter-spacing: -0.04em; }
  .detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(2,27,46,.06); font-size: 13px; }
  .detail-row:last-child { border-bottom: 0; }
  .detail-key { color: rgba(2,27,46,.5); }
  .detail-val { font-weight: 600; }
  .btn { display: inline-block; background: #65a1d8; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; margin-top: 8px; }
  .card-footer { padding: 20px 32px; background: rgba(2,27,46,.02); border-top: 1px solid rgba(2,27,46,.06); font-size: 12px; color: rgba(2,27,46,.4); text-align: center; }
</style>
</head>
<body>
<div class="card">
  <div class="card-header">
    <div class="brand">{{ config('app.name') }}</div>
  </div>
  <div class="card-body">
    <h2>Your invoice is ready</h2>
    <p>Thank you for your payment. Your invoice is attached to this email for your records.</p>

    <div class="amount-box">
      <div class="amount-label">Amount Paid</div>
      @php $sym = match(strtolower($invoice->currency ?? 'usd')) { 'inr' => '₹', 'eur' => '€', 'gbp' => '£', default => '$' }; @endphp
      <div class="amount-value">{{ $sym }}{{ number_format(($invoice->total ?: $invoice->amount) / 100, 2) }}</div>
    </div>

    <div>
      <div class="detail-row">
        <span class="detail-key">Invoice Number</span>
        <span class="detail-val">{{ $invoice->invoice_number }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-key">Date</span>
        <span class="detail-val">{{ $invoice->created_at->format('M j, Y') }}</span>
      </div>
      @if($invoice->subscription?->plan)
        <div class="detail-row">
          <span class="detail-key">Plan</span>
          <span class="detail-val">{{ $invoice->subscription->plan->name }}</span>
        </div>
      @endif
      <div class="detail-row">
        <span class="detail-key">Status</span>
        <span class="detail-val" style="color:#059669">{{ ucfirst($invoice->status) }}</span>
      </div>
    </div>

    <a href="{{ route('billing.invoices') }}" class="btn">View All Invoices</a>
  </div>
  <div class="card-footer">
    {{ config('app.name') }} · <a href="{{ route('billing.index') }}" style="color:inherit">Billing Dashboard</a>
  </div>
</div>
</body>
</html>
