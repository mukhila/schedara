@extends('layouts.backend')
@section('title', 'ROI Analytics')

@section('content')

<div class="mb-4">
  <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Analytics</div>
  <h1 class="text-3xl font-extrabold tracking-tight text-ink">ROI Calculator</h1>
</div>

@include('backend.analytics._nav')
@include('backend.analytics._filter_bar')

@php
  $roi = $summary['roi'] ?? 0;
  $isPositive = $roi >= 0;
@endphp

{{-- Main ROI KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="card p-5 {{ $isPositive ? 'border-mint/40' : 'border-coral/40' }}">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">ROI</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight {{ $isPositive ? 'text-mint' : 'text-coral' }}">
      {{ $isPositive ? '+' : '' }}{{ number_format($roi, 1) }}%
    </div>
    <div class="text-xs text-ink/50 mt-1">Return on investment</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">ROAS</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($summary['roas'] ?? 0, 2) }}×</div>
    <div class="text-xs text-ink/50 mt-1">Return on ad spend</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Total Spend</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">${{ number_format($summary['total_spend'] ?? 0, 0) }}</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Total Revenue</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight text-mint">${{ number_format($summary['total_revenue'] ?? 0, 0) }}</div>
    <div class="text-xs text-ink/50 mt-1">Net: ${{ number_format($summary['net_profit'] ?? 0, 0) }}</div>
  </div>
</div>

<div class="grid lg:grid-cols-[1.7fr_1fr] gap-4 mb-6">

  {{-- Spend vs Revenue chart --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">Spend vs Revenue by platform</h3>
    <p class="text-xs text-ink/50 mb-4">Campaign-level breakdown</p>
    @if(count($byPlatform) > 0)
    <canvas id="roiChart" style="height:280px"></canvas>
    @else
    <div class="h-60 flex items-center justify-center text-ink/30 text-sm">No campaign data for this period</div>
    @endif
  </div>

  {{-- Campaign-level metrics --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-4">Campaign averages</h3>
    <div class="space-y-4">
      @foreach([
        ['label' => 'Avg ROI',         'value' => number_format($summary['campaigns']['avg_roi'] ?? 0, 1) . '%'],
        ['label' => 'Avg ROAS',        'value' => number_format($summary['campaigns']['avg_roas'] ?? 0, 2) . '×'],
        ['label' => 'Avg CTR',         'value' => number_format($summary['campaigns']['avg_ctr'] ?? 0, 2) . '%'],
        ['label' => 'Avg CPC',         'value' => '$' . number_format($summary['campaigns']['avg_cpc'] ?? 0, 2)],
        ['label' => 'Total Conversions','value' => number_format($summary['total_conversions'] ?? 0)],
        ['label' => 'CPA',             'value' => '$' . number_format($summary['cpa'] ?? 0, 2)],
      ] as $m)
      <div class="flex items-center justify-between py-2 border-b border-line last:border-0">
        <span class="text-sm text-ink/70">{{ $m['label'] }}</span>
        <span class="font-bold text-sm">{{ $m['value'] }}</span>
      </div>
      @endforeach
    </div>
  </div>

</div>

{{-- Platform table --}}
@if(count($byPlatform) > 0)
<div class="card p-6">
  <h3 class="text-lg font-bold mb-4">ROI by platform</h3>
  <table class="w-full text-sm">
    <thead>
      <tr class="text-[10px] font-bold uppercase tracking-wider text-ink/40 text-left border-b border-line">
        <th class="py-2">Platform</th>
        <th class="py-2 text-right">Spend</th>
        <th class="py-2 text-right">Revenue</th>
        <th class="py-2 text-right">Net Profit</th>
        <th class="py-2 text-right">ROI</th>
        <th class="py-2 text-right">Conversions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-line">
      @foreach($byPlatform as $p)
      @php $net = $p['revenue'] - $p['spend']; @endphp
      <tr class="hover:bg-paper/80 transition-colors">
        <td class="py-3 font-bold capitalize">{{ $p['platform'] }}</td>
        <td class="py-3 text-right">${{ number_format($p['spend'], 0) }}</td>
        <td class="py-3 text-right">${{ number_format($p['revenue'], 0) }}</td>
        <td class="py-3 text-right {{ $net >= 0 ? 'text-mint' : 'text-coral' }} font-semibold">${{ number_format($net, 0) }}</td>
        <td class="py-3 text-right">
          <span class="pill {{ $p['avg_roi'] >= 0 ? 'pill-mint' : 'pill-coral' }}">{{ number_format($p['avg_roi'], 1) }}%</span>
        </td>
        <td class="py-3 text-right">{{ number_format($p['conversions']) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const PL = @json($byPlatform);

if (PL.length) {
  new Chart(document.getElementById('roiChart'), {
    type:'bar',
    data:{
      labels: PL.map(p=>p.platform),
      datasets:[
        { label:'Spend',   data:PL.map(p=>p.spend),   backgroundColor:'rgba(255,64,28,.6)',  borderRadius:4 },
        { label:'Revenue', data:PL.map(p=>p.revenue), backgroundColor:'rgba(34,176,126,.7)', borderRadius:4 },
      ]
    },
    options:{
      responsive:true,maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{font:{size:11}}}},
      scales:{ x:{grid:{display:false},ticks:{color:'#021b2e66',font:{size:11}}},
               y:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11},callback:v=>'$'+v.toLocaleString()}} }
    }
  });
}
</script>
@endpush
