<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Your trial is ending soon</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; padding: 32px 16px; color: #021b2e; }
  .card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid rgba(2,27,46,.1); }
  .card-header { padding: 28px 32px; background: linear-gradient(135deg, #1a3a5c, #65a1d8); }
  .brand { font-size: 20px; font-weight: 800; color: #fff; }
  .card-body { padding: 32px; }
  h2 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
  p { font-size: 14px; line-height: 1.7; color: rgba(2,27,46,.65); margin-bottom: 16px; }
  .trial-box { background: rgba(245,158,11,.06); border: 1px solid rgba(245,158,11,.2); border-radius: 12px; padding: 20px 24px; margin: 20px 0; }
  .trial-days { font-size: 28px; font-weight: 800; color: #d97706; }
  .trial-sub { font-size: 13px; color: rgba(2,27,46,.5); }
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
    <h2>Your trial is ending soon</h2>
    <p>Your free trial for the <strong>{{ $subscription->plan->name ?? 'current' }}</strong> plan is coming to an end.</p>

    @if($subscription->trial_ends_at)
      <div class="trial-box">
        <div class="trial-days">{{ $subscription->trialDaysRemaining() }} days left</div>
        <div class="trial-sub">Trial ends {{ $subscription->trial_ends_at->format('M j, Y') }}</div>
      </div>
    @endif

    <p>Subscribe now to keep access to all your data and avoid any interruption to your workflow.</p>
    <a href="{{ route('billing.plans') }}" class="btn">Subscribe Now</a>
  </div>
  <div class="card-footer">{{ config('app.name') }} · <a href="{{ route('billing.index') }}" style="color:inherit">Billing Dashboard</a></div>
</div>
</body>
</html>
