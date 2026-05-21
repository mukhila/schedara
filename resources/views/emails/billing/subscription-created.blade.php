<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Welcome to {{ $subscription->plan->name ?? 'your plan' }}</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; padding: 32px 16px; color: #021b2e; }
  .card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid rgba(2,27,46,.1); }
  .card-header { padding: 28px 32px; background: linear-gradient(135deg, #021b2e, #1a3a5c); }
  .brand { font-size: 20px; font-weight: 800; color: #fff; }
  .card-body { padding: 32px; }
  h2 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
  p { font-size: 14px; line-height: 1.7; color: rgba(2,27,46,.65); margin-bottom: 16px; }
  .plan-box { background: rgba(101,161,216,.06); border: 1px solid rgba(101,161,216,.2); border-radius: 12px; padding: 20px 24px; margin: 24px 0; }
  .plan-name { font-size: 18px; font-weight: 800; color: #021b2e; }
  .plan-sub { font-size: 13px; color: rgba(2,27,46,.5); margin-top: 4px; }
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
    <h2>Your subscription is active</h2>
    <p>Welcome! Your subscription has been successfully activated. You now have access to all features included in your plan.</p>

    <div class="plan-box">
      <div class="plan-name">{{ $subscription->plan->name ?? 'Your Plan' }}</div>
      <div class="plan-sub">
        {{ ucfirst($subscription->interval ?? 'monthly') }} billing
        @if($subscription->isOnTrial())
          · Trial ends {{ $subscription->trial_ends_at->format('M j, Y') }}
        @elseif($subscription->current_period_end)
          · Next renewal {{ $subscription->current_period_end->format('M j, Y') }}
        @endif
      </div>
    </div>

    <a href="{{ route('billing.index') }}" class="btn">Go to Billing Dashboard</a>
  </div>
  <div class="card-footer">
    {{ config('app.name') }} · <a href="{{ route('billing.plans') }}" style="color:inherit">Manage Plans</a>
  </div>
</div>
</body>
</html>
