<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Action Required: Payment Failed</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; padding: 32px 16px; color: #021b2e; }
  .card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid rgba(239,68,68,.2); }
  .card-header { padding: 28px 32px; background: linear-gradient(135deg, #7f1d1d, #dc2626); }
  .brand { font-size: 20px; font-weight: 800; color: #fff; }
  .card-body { padding: 32px; }
  h2 { font-size: 20px; font-weight: 800; margin-bottom: 8px; color: #dc2626; }
  p { font-size: 14px; line-height: 1.7; color: rgba(2,27,46,.65); margin-bottom: 16px; }
  .btn { display: inline-block; background: #dc2626; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; margin-top: 8px; }
  .card-footer { padding: 20px 32px; background: rgba(2,27,46,.02); border-top: 1px solid rgba(2,27,46,.06); font-size: 12px; color: rgba(2,27,46,.4); text-align: center; }
</style>
</head>
<body>
<div class="card">
  <div class="card-header">
    <div class="brand">{{ config('app.name') }}</div>
  </div>
  <div class="card-body">
    <h2>Payment Failed</h2>
    <p>We were unable to process your payment for the <strong>{{ $subscription->plan->name ?? 'current' }}</strong> plan.</p>
    <p>Please update your payment method to avoid service interruption. We will retry the payment automatically.</p>
    <a href="{{ route('billing.index') }}" class="btn">Update Payment Method</a>
  </div>
  <div class="card-footer">{{ config('app.name') }} Support</div>
</div>
</body>
</html>
