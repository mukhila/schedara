{{--
  Widget: Revenue & ROI
  Vars: $roiSummary, $campaigns, $dateRange
--}}
@php
  $roi      = $roiSummary['roi'] ?? ($campaigns['avg_roi'] ?? 0);
  $revenue  = $roiSummary['total_revenue'] ?? ($campaigns['total_revenue'] ?? 0);
  $spend    = $roiSummary['total_spend'] ?? ($campaigns['total_spend'] ?? 0);
  $profit   = $roiSummary['net_profit'] ?? ($revenue - $spend);
  $roas     = $roiSummary['roas'] ?? ($spend > 0 ? round($revenue / $spend, 2) : 0);
  $cpa      = $roiSummary['cpa'] ?? 0;
  $convs    = $roiSummary['total_conversions'] ?? ($campaigns['total_conversions'] ?? 0);
@endphp

<div class="grid lg:grid-cols-[1fr_1fr] gap-4">

  {{-- Revenue hero card --}}
  <div class="card p-6 relative overflow-hidden" style="background:linear-gradient(135deg,#021b2e 0%,#18406d 100%);border-color:transparent">
    <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-brand-400/20 blur-2xl pointer-events-none"></div>
    <div class="relative">
      <div class="inline-flex items-center gap-1.5 bg-white/10 text-brand-300 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full mb-4">
        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1l1.6 4.4L16 7l-4.4 1.6L10 13l-1.6-4.4L4 7l4.4-1.6z"/></svg>
        Revenue & ROI · {{ $dateRange['days'] ?? 30 }}d
      </div>
      <div class="text-white/50 text-[11px] uppercase tracking-wider font-bold mb-1">Total Revenue</div>
      <div class="text-4xl font-extrabold text-white tracking-tight">${{ number_format($revenue, 0) }}</div>
      <div class="mt-4 grid grid-cols-2 gap-4">
        <div>
          <div class="text-white/50 text-[10px] uppercase tracking-wider">Spend</div>
          <div class="text-white font-bold text-lg">${{ number_format($spend, 0) }}</div>
        </div>
        <div>
          <div class="text-white/50 text-[10px] uppercase tracking-wider">Net Profit</div>
          <div class="{{ $profit >= 0 ? 'text-brand-300' : 'text-coral' }} font-bold text-lg">${{ number_format($profit, 0) }}</div>
        </div>
        <div>
          <div class="text-white/50 text-[10px] uppercase tracking-wider">Avg ROI</div>
          <div class="{{ $roi >= 0 ? 'text-brand-300' : 'text-coral' }} font-bold text-lg">{{ number_format($roi, 1) }}%</div>
        </div>
        <div>
          <div class="text-white/50 text-[10px] uppercase tracking-wider">Conversions</div>
          <div class="text-white font-bold text-lg">{{ number_format($convs) }}</div>
        </div>
      </div>
      <a href="{{ route('analytics.roi') }}" class="mt-5 inline-flex items-center gap-1.5 text-brand-300 text-xs font-bold hover:text-white transition-colors">
        View ROI details
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 10h12M11 5l5 5-5 5" stroke-linecap="round"/></svg>
      </a>
    </div>
  </div>

  {{-- ROAS + CPA metrics --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-4">Ad efficiency</h3>
    <div class="space-y-5">
      <div>
        <div class="flex justify-between text-sm mb-1.5">
          <span class="font-semibold text-ink/70">ROAS (Return on Ad Spend)</span>
          <span class="font-extrabold text-ink">{{ number_format($roas, 2) }}x</span>
        </div>
        @php $roasPct = min(100, $roas > 0 ? ($roas / 10 * 100) : 0); @endphp
        <div class="h-2 bg-line rounded-full overflow-hidden">
          <div class="h-full rounded-full bg-brand-400 transition-all duration-700" style="width:{{ $roasPct }}%"></div>
        </div>
        <p class="text-xs text-ink/40 mt-1">{{ $roas >= 4 ? 'Excellent' : ($roas >= 2 ? 'Good' : ($roas >= 1 ? 'Break-even' : 'Below break-even')) }}</p>
      </div>

      <div>
        <div class="flex justify-between text-sm mb-1.5">
          <span class="font-semibold text-ink/70">Cost per Acquisition (CPA)</span>
          <span class="font-extrabold text-ink">${{ number_format($cpa, 2) }}</span>
        </div>
        <p class="text-xs text-ink/40">{{ $convs }} total conversions</p>
      </div>

      @if(!empty($campaigns['total_campaigns']) && $campaigns['total_campaigns'] > 0)
      <div class="pt-4 border-t border-line">
        <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-2">Active campaigns</div>
        <div class="text-2xl font-extrabold text-ink">{{ number_format($campaigns['total_campaigns']) }}</div>
        <a href="{{ route('analytics.campaigns') }}" class="mt-2 text-xs font-bold text-brand-600 hover:text-brand-700 transition-colors flex items-center gap-1">
          View campaigns →
        </a>
      </div>
      @endif
    </div>
  </div>

</div>
