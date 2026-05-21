@extends('layouts.backend')
@section('page_title', 'Billing')

@section('styles')
<style>
.billing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;margin-bottom:1.5rem}
.stat-card{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);padding:1.5rem}
.stat-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(2,27,46,.4);margin-bottom:.5rem}
.stat-value{font-size:1.75rem;font-weight:800;color:#021b2e;letter-spacing:-.03em}
.stat-sub{font-size:.8rem;color:rgba(2,27,46,.45);margin-top:.25rem}
.section-card{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);overflow:hidden;margin-bottom:1.5rem}
.section-header{padding:1rem 1.25rem;border-bottom:1px solid rgba(2,27,46,.07);display:flex;align-items:center;justify-content:space-between}
.section-title{font-size:.9rem;font-weight:800;color:#021b2e}
.invoice-table{width:100%;border-collapse:collapse}
.invoice-table th{text-align:left;padding:.625rem 1rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(2,27,46,.45);border-bottom:2px solid rgba(2,27,46,.08)}
.invoice-table td{padding:.875rem 1rem;border-bottom:1px solid rgba(2,27,46,.06);font-size:.875rem;vertical-align:middle}
.invoice-table tr:last-child td{border-bottom:0}
.badge{font-size:.7rem;font-weight:700;padding:.2rem .55rem;border-radius:5px;white-space:nowrap}
.badge-paid{background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.2)}
.badge-open{background:rgba(245,158,11,.1);color:#d97706;border:1px solid rgba(245,158,11,.2)}
.badge-void{background:rgba(2,27,46,.06);color:rgba(2,27,46,.4);border:1px solid rgba(2,27,46,.1)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:.625rem 1.25rem;border-radius:9px;font-family:inherit;font-size:.875rem;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:.2s}
.btn-primary{background:#65a1d8;color:#fff}.btn-primary:hover{background:#4a8ccc}
.btn-outline{background:#fff;color:#021b2e;border:1px solid rgba(2,27,46,.2)}.btn-outline:hover{border-color:#65a1d8;color:#65a1d8}
.btn-sm{padding:.4rem .875rem;font-size:.8rem}
.plan-chip{display:inline-flex;align-items:center;gap:.5rem;background:rgba(101,161,216,.08);border:1px solid rgba(101,161,216,.2);border-radius:8px;padding:.4rem .85rem;font-size:.82rem;font-weight:700;color:#65a1d8}
.plan-chip.trial{background:rgba(34,176,126,.08);border-color:rgba(34,176,126,.2);color:#22B07E}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}
.flash-success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.25);color:#15803d}
.flash-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#dc2626}
.progress-bar{height:6px;background:rgba(2,27,46,.08);border-radius:99px;overflow:hidden;margin-top:.5rem}
.progress-fill{height:100%;background:#65a1d8;border-radius:99px;transition:width .4s}
.empty-state{text-align:center;padding:3rem 1rem;color:rgba(2,27,46,.35);font-size:.875rem}
</style>
@endsection

@section('topbar_actions')
@if($subscription?->isActive())
  <form method="POST" action="{{ route('billing.portal') }}">
    @csrf
    <button type="submit" class="btn btn-outline btn-sm">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
      Manage Billing
    </button>
  </form>
@endif
<a href="{{ route('billing.plans') }}" class="btn btn-primary btn-sm">
  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
  Upgrade Plan
</a>
@endsection

@section('content')

@if(session('success'))
  <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="flash flash-error">{{ session('error') }}</div>
@endif

{{-- ── Stat Cards ── --}}
<div class="billing-grid">

  {{-- Current Plan --}}
  <div class="stat-card">
    <div class="stat-label">Current Plan</div>
    @if($plan)
      <div style="margin-top:.25rem">
        <span class="plan-chip {{ $tenant->isOnTrial() ? 'trial' : '' }}">
          @if($tenant->isOnTrial())
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l2.4 5.6L20 8.6l-4.4 4 1.4 5.8L12 15l-5 3 1.4-5.8L4 8.6l5.6-.6L12 2z"/></svg>
            Trial — {{ $plan->name }}
          @else
            {{ $plan->name }}
          @endif
        </span>
      </div>
      @if($tenant->isOnTrial())
        <div class="stat-sub" style="margin-top:.5rem">
          Trial ends {{ $tenant->trial_ends_at->diffForHumans() }}
        </div>
      @elseif($subscription?->isActive())
        <div class="stat-sub" style="margin-top:.5rem">
          Renews {{ $subscription->current_period_end?->format('M j, Y') }}
          · {{ ucfirst($subscription->interval) }}
        </div>
      @endif
    @else
      <div class="stat-value" style="font-size:1.1rem;margin-top:.35rem">No plan</div>
    @endif
  </div>

  {{-- Subscription Status --}}
  <div class="stat-card">
    <div class="stat-label">Status</div>
    <div class="stat-value" style="font-size:1.2rem;margin-top:.35rem">
      @php $status = $subscription?->status ?? ($tenant->isOnTrial() ? 'trialing' : 'inactive'); @endphp
      @if(in_array($status, ['active','trialing']))
        <span style="color:#059669">● Active</span>
      @elseif($status === 'past_due')
        <span style="color:#d97706">● Past Due</span>
      @elseif($status === 'cancelled')
        <span style="color:#dc2626">● Cancelled</span>
      @else
        <span style="color:rgba(2,27,46,.35)">● Inactive</span>
      @endif
    </div>
    @if($subscription?->cancel_at)
      <div class="stat-sub">Cancels {{ $subscription->cancel_at->diffForHumans() }}</div>
    @elseif($subscription?->isActive())
      <div class="stat-sub">{{ $subscription->daysUntilRenewal() }} days until renewal</div>
    @endif
  </div>

  {{-- Next Invoice --}}
  <div class="stat-card">
    <div class="stat-label">Next Invoice</div>
    @if($subscription?->isActive() && $plan)
      <div class="stat-value">
        @if($subscription->isYearly())
          {{ $plan->currency === 'inr' ? '₹' : '$' }}{{ number_format($plan->yearlyPrice(), 2) }}
        @else
          {{ $plan->currency === 'inr' ? '₹' : '$' }}{{ number_format($plan->monthlyPrice(), 2) }}
        @endif
      </div>
      <div class="stat-sub">Due {{ $subscription->current_period_end?->format('M j, Y') }}</div>
    @else
      <div class="stat-value" style="font-size:1.1rem;margin-top:.35rem">—</div>
    @endif
  </div>

  {{-- Provider --}}
  <div class="stat-card">
    <div class="stat-label">Payment Method</div>
    <div style="margin-top:.5rem">
      @if($subscription?->isStripe())
        <div style="display:flex;align-items:center;gap:.5rem">
          <span style="font-size:1.4rem">💳</span>
          <div>
            <div style="font-weight:700;font-size:.9rem">Stripe</div>
            <div class="stat-sub">Card payments (USD)</div>
          </div>
        </div>
      @elseif($subscription?->isRazorpay())
        <div style="display:flex;align-items:center;gap:.5rem">
          <span style="font-size:1.4rem">🇮🇳</span>
          <div>
            <div style="font-weight:700;font-size:.9rem">Razorpay</div>
            <div class="stat-sub">UPI / Card (INR)</div>
          </div>
        </div>
      @else
        <div style="font-weight:700;font-size:.9rem;color:rgba(2,27,46,.35)">None configured</div>
      @endif
    </div>
  </div>

</div>

{{-- ── Invoice History ── --}}
<div class="section-card">
  <div class="section-header">
    <span class="section-title">Invoice History</span>
    <span style="font-size:.8rem;color:rgba(2,27,46,.4)">Last {{ $invoices->count() }} invoices</span>
  </div>

  @if($invoices->isEmpty())
    <div class="empty-state">No invoices yet.</div>
  @else
    <table class="invoice-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Provider</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoices as $invoice)
          <tr>
            <td style="color:rgba(2,27,46,.55)">{{ $invoice->created_at->format('M j, Y') }}</td>
            <td>
              {{ $invoice->description ?? 'Subscription payment' }}
              @if($invoice->period_start && $invoice->period_end)
                <div style="font-size:.75rem;color:rgba(2,27,46,.4)">
                  {{ $invoice->period_start->format('M j') }} – {{ $invoice->period_end->format('M j, Y') }}
                </div>
              @endif
            </td>
            <td style="font-weight:700">{{ $invoice->formattedAmount() }}</td>
            <td>
              <span class="badge badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </td>
            <td style="color:rgba(2,27,46,.45);font-size:.8rem">{{ ucfirst($invoice->provider) }}</td>
            <td style="text-align:right">
              @if($invoice->invoice_pdf_url)
                <a href="{{ $invoice->invoice_pdf_url }}" target="_blank" class="btn btn-outline btn-sm">
                  PDF
                </a>
              @elseif($invoice->hosted_invoice_url)
                <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="btn btn-outline btn-sm">
                  View
                </a>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

@if($subscription && !$subscription->isCancelled())
<div style="background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);padding:1.5rem">
  <div style="font-size:.9rem;font-weight:800;color:#021b2e;margin-bottom:.35rem">Cancel Subscription</div>
  <p style="font-size:.82rem;color:rgba(2,27,46,.5);margin-bottom:1rem">
    You can cancel anytime. Your plan remains active until the end of the current billing period.
    @if($subscription->isStripe())
      Manage cancellation via the Stripe billing portal.
    @endif
  </p>
  @if($subscription->isStripe())
    <form method="POST" action="{{ route('billing.portal') }}">
      @csrf
      <button type="submit" class="btn btn-outline btn-sm" style="color:#dc2626;border-color:rgba(239,68,68,.3)">
        Open Billing Portal to Cancel
      </button>
    </form>
  @endif
</div>
@endif

@endsection
