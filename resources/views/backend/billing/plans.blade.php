@extends('layouts.backend')
@section('page_title', 'Choose a Plan')

@section('styles')
<style>
.plans-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;margin-bottom:2rem}
.plan-card{background:#fff;border-radius:16px;border:2px solid rgba(2,27,46,.08);padding:1.75rem;position:relative;transition:.2s;display:flex;flex-direction:column}
.plan-card:hover{border-color:#65a1d8;box-shadow:0 4px 24px rgba(101,161,216,.12)}
.plan-card.current{border-color:#65a1d8;background:linear-gradient(135deg,rgba(101,161,216,.04),#fff)}
.plan-card.popular{border-color:#8b5cf6}
.popular-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#8b5cf6;color:#fff;font-size:.7rem;font-weight:800;padding:.25rem .75rem;border-radius:99px;white-space:nowrap;letter-spacing:.04em}
.plan-name{font-size:1.05rem;font-weight:800;color:#021b2e;margin-bottom:.25rem}
.plan-desc{font-size:.8rem;color:rgba(2,27,46,.45);margin-bottom:1.25rem}
.plan-price{font-size:2.2rem;font-weight:800;color:#021b2e;letter-spacing:-.04em;line-height:1}
.plan-price sup{font-size:1rem;top:-.5em;margin-right:1px}
.plan-interval{font-size:.78rem;color:rgba(2,27,46,.4);margin-top:.2rem}
.plan-features{list-style:none;padding:0;margin:1.25rem 0;flex:1}
.plan-features li{font-size:.82rem;color:rgba(2,27,46,.7);padding:.3rem 0;display:flex;align-items:center;gap:.5rem;border-bottom:1px solid rgba(2,27,46,.04)}
.plan-features li:last-child{border-bottom:0}
.plan-features li .check{color:#10b981;flex-shrink:0}
.plan-features li .cross{color:rgba(2,27,46,.2);flex-shrink:0}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:.7rem 1.25rem;border-radius:9px;font-family:inherit;font-size:.875rem;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:.2s;width:100%}
.btn-primary{background:#65a1d8;color:#fff}.btn-primary:hover{background:#4a8ccc}
.btn-purple{background:#8b5cf6;color:#fff}.btn-purple:hover{background:#7c3aed}
.btn-outline{background:#fff;color:#021b2e;border:1.5px solid rgba(2,27,46,.2)}.btn-outline:hover{border-color:#65a1d8}
.btn-ghost{background:rgba(2,27,46,.04);color:rgba(2,27,46,.5);border:none;cursor:default}
.interval-toggle{display:flex;align-items:center;gap:.75rem;margin-bottom:1.75rem}
.toggle-label{font-size:.875rem;font-weight:600;color:rgba(2,27,46,.5);cursor:pointer}
.toggle-label.active{color:#021b2e;font-weight:700}
.toggle-switch{position:relative;width:44px;height:24px;cursor:pointer}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-track{position:absolute;inset:0;background:rgba(2,27,46,.15);border-radius:99px;transition:.3s}
.toggle-switch input:checked + .toggle-track{background:#65a1d8}
.toggle-thumb{position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;top:3px;left:3px;transition:.3s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle-switch input:checked ~ .toggle-thumb{left:23px}
.discount-badge{background:rgba(34,176,126,.1);color:#22B07E;font-size:.72rem;font-weight:700;padding:.2rem .5rem;border-radius:5px;border:1px solid rgba(34,176,126,.2)}
.provider-tabs{display:flex;gap:.5rem;margin-bottom:1.5rem}
.provider-tab{padding:.5rem 1rem;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;border:1.5px solid rgba(2,27,46,.12);background:#fff;color:rgba(2,27,46,.5);transition:.2s}
.provider-tab.active{border-color:#65a1d8;color:#65a1d8;background:rgba(101,161,216,.06)}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}
.flash-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#dc2626}
</style>
@endsection

@section('content')

@if(session('error'))
  <div class="flash flash-error">{{ session('error') }}</div>
@endif

<div style="margin-bottom:1rem">
  <a href="{{ route('billing.index') }}" style="font-size:.82rem;color:rgba(2,27,46,.45);text-decoration:none">
    ← Back to billing
  </a>
</div>

{{-- ── Interval Toggle ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem">
  <div class="interval-toggle" id="intervalToggle">
    <span class="toggle-label active" id="labelMonthly">Monthly</span>
    <label class="toggle-switch">
      <input type="checkbox" id="intervalSwitch">
      <div class="toggle-track"></div>
      <div class="toggle-thumb"></div>
    </label>
    <span class="toggle-label" id="labelYearly">
      Yearly
      @php $maxDiscount = $plans->max(fn ($p) => $p->yearlyDiscount()); @endphp
      @if($maxDiscount > 0)
        <span class="discount-badge">Save up to {{ $maxDiscount }}%</span>
      @endif
    </span>
  </div>

  {{-- Provider Tabs --}}
  <div class="provider-tabs" id="providerTabs">
    <button class="provider-tab active" data-provider="stripe" onclick="setProvider('stripe')">
      💳 USD · Stripe
    </button>
    <button class="provider-tab" data-provider="razorpay" onclick="setProvider('razorpay')">
      🇮🇳 INR · Razorpay
    </button>
  </div>
</div>

{{-- ── Plans Grid ── --}}
<div class="plans-grid">
  @foreach($plans as $i => $plan)
    @php
      $isCurrent = $plan->id === $currentPlanId;
      $isPopular  = $i === 1; // second plan is "popular"
      $discount   = $plan->yearlyDiscount();
    @endphp
    <div class="plan-card {{ $isCurrent ? 'current' : '' }} {{ $isPopular ? 'popular' : '' }}">
      @if($isPopular)
        <div class="popular-badge">Most Popular</div>
      @endif

      <div class="plan-name">{{ $plan->name }}</div>
      <div class="plan-desc">
        @if($plan->slug === 'free') Perfect for getting started
        @elseif($plan->slug === 'starter') For small teams growing fast
        @elseif($plan->slug === 'pro') Everything you need to scale
        @else Enterprise-grade power
        @endif
      </div>

      {{-- Price display --}}
      <div class="price-monthly">
        <div class="plan-price">
          <sup class="currency-usd">$</sup><sup class="currency-inr" style="display:none">₹</sup>
          <span class="price-val" data-monthly="{{ $plan->monthlyPrice() }}" data-yearly="{{ $plan->yearlyPrice() }}">
            {{ number_format($plan->monthlyPrice(), 0) }}
          </span>
        </div>
        <div class="plan-interval">
          per month
          @if($discount > 0)
            <span class="discount-badge yearly-discount" style="display:none">{{ $discount }}% off</span>
          @endif
        </div>
      </div>

      {{-- Features --}}
      <ul class="plan-features">
        @foreach($plan->features ?? [] as $key => $value)
          <li>
            @if($value)
              <svg class="check" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7" stroke-linecap="round"/></svg>
            @else
              <svg class="cross" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            @endif
            {{ str_replace('_', ' ', ucfirst($key)) }}
          </li>
        @endforeach
        @if($plan->getLimit('team_members'))
          <li>
            <svg class="check" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7" stroke-linecap="round"/></svg>
            Up to {{ $plan->getLimit('team_members') === -1 ? 'unlimited' : $plan->getLimit('team_members') }} team members
          </li>
        @endif
      </ul>

      {{-- CTA --}}
      @if($isCurrent)
        <button class="btn btn-ghost" disabled>Current Plan</button>
      @elseif($plan->price_monthly === 0)
        <form method="POST" action="{{ route('billing.checkout') }}">
          @csrf
          <input type="hidden" name="plan_id" value="{{ $plan->id }}">
          <input type="hidden" name="interval" value="monthly">
          <input type="hidden" name="provider" value="stripe">
          <button type="submit" class="btn btn-outline">Downgrade to Free</button>
        </form>
      @else
        <form method="POST" action="{{ route('billing.checkout') }}" class="checkout-form">
          @csrf
          <input type="hidden" name="plan_id" value="{{ $plan->id }}">
          <input type="hidden" name="interval" value="monthly" class="interval-input">
          <input type="hidden" name="provider" value="stripe" class="provider-input">
          <button type="submit" class="btn {{ $isPopular ? 'btn-purple' : 'btn-primary' }}">
            {{ $currentPlanId && $plan->price_monthly > ($plans->firstWhere('id', $currentPlanId)?->price_monthly ?? 0) ? 'Upgrade' : 'Choose' }} {{ $plan->name }}
          </button>
        </form>
      @endif
    </div>
  @endforeach
</div>

<p style="text-align:center;font-size:.78rem;color:rgba(2,27,46,.35);margin-top:-.5rem">
  All plans include a 14-day free trial. No credit card required for free plan. Cancel anytime.
</p>

@endsection

@section('scripts')
<script>
let currentInterval = 'monthly';
let currentProvider = 'stripe';

document.getElementById('intervalSwitch').addEventListener('change', function() {
  currentInterval = this.checked ? 'yearly' : 'monthly';
  updateUI();
});

function setProvider(provider) {
  currentProvider = provider;
  document.querySelectorAll('.provider-tab').forEach(t => t.classList.toggle('active', t.dataset.provider === provider));
  updateUI();
}

function updateUI() {
  const yearly = currentInterval === 'yearly';
  const inr    = currentProvider === 'razorpay';

  document.getElementById('labelMonthly').classList.toggle('active', !yearly);
  document.getElementById('labelYearly').classList.toggle('active', yearly);

  // Update prices
  document.querySelectorAll('.price-val').forEach(el => {
    const price = yearly ? parseFloat(el.dataset.yearly) : parseFloat(el.dataset.monthly);
    el.textContent = Math.floor(price).toLocaleString();
  });

  document.querySelectorAll('.currency-usd').forEach(el => el.style.display = inr ? 'none' : '');
  document.querySelectorAll('.currency-inr').forEach(el => el.style.display = inr ? '' : 'none');

  document.querySelectorAll('.yearly-discount').forEach(el => {
    el.style.display = yearly ? '' : 'none';
  });

  // Update form inputs
  document.querySelectorAll('.interval-input').forEach(el => el.value = currentInterval);
  document.querySelectorAll('.provider-input').forEach(el => el.value = currentProvider);
}
</script>
@endsection
