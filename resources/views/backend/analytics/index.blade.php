@extends('layouts.backend')
@section('title', 'Analytics Overview')

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-4">
  <div>
    <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Analytics</div>
    <h1 class="text-3xl font-extrabold tracking-tight text-ink">Overview</h1>
  </div>
  <a href="{{ route('analytics.reports') }}" class="btn-primary flex items-center gap-1.5">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    Generate Report
  </a>
</div>

@include('backend.analytics._nav')
@include('backend.analytics._filter_bar')

{{-- KPI row --}}
@php
  $totalEngagement = ($engagement['kpi']['total_likes'] ?? 0)
    + ($engagement['kpi']['total_comments'] ?? 0)
    + ($engagement['kpi']['total_shares'] ?? 0)
    + ($engagement['kpi']['total_saves'] ?? 0);
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  @foreach([
    ['label' => 'Reach',      'value' => number_format($reach['kpi']['reach'] ?? 0),       'sub' => number_format($reach['kpi']['impressions'] ?? 0) . ' impressions', 'pill' => 'pill-mint'],
    ['label' => 'Engagement', 'value' => number_format($totalEngagement),                   'sub' => number_format($engagement['kpi']['avg_engagement_rate'] ?? 0, 2) . '% avg rate',    'pill' => 'pill-mint'],
    ['label' => 'Followers',  'value' => number_format($followers['kpi']['total_followers'] ?? 0), 'sub' => ($followers['kpi']['net_growth'] ?? 0 >= 0 ? '+' : '') . number_format($followers['kpi']['net_growth'] ?? 0) . ' net growth', 'pill' => 'pill-brand'],
    ['label' => 'Clicks',     'value' => number_format($reach['kpi']['clicks'] ?? 0),       'sub' => number_format($reach['kpi']['ctr'] ?? 0, 2) . '% CTR',             'pill' => 'pill-gold'],
  ] as $card)
  <div class="card p-5">
    <div class="flex items-center justify-between">
      <div class="text-xs font-bold uppercase tracking-wider text-ink/50">{{ $card['label'] }}</div>
      <span class="pill {{ $card['pill'] }}">{{ $card['value'] }}</span>
    </div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $card['value'] }}</div>
    <div class="text-xs text-ink/50 mt-1">{{ $card['sub'] }}</div>
  </div>
  @endforeach
</div>

{{-- Time series + platform --}}
<div class="grid lg:grid-cols-[1.7fr_1fr] gap-4 mb-6">
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">Performance over time</h3>
    <p class="text-xs text-ink/50 mb-4">{{ $filter->range->fromString() }} – {{ $filter->range->toString() }}</p>
    <canvas id="tsChart" style="height:240px"></canvas>
  </div>
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">By platform</h3>
    <p class="text-xs text-ink/50 mb-4">Engagement share</p>
    <canvas id="platformChart" style="height:240px"></canvas>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const TS  = @json($engagement['time_series'] ?? []);
const PL  = @json($engagement['by_platform'] ?? []);
const COLORS = {instagram:'#65a1d8',facebook:'#021b2e',twitter:'#2f76bd',linkedin:'#8bb4dc',tiktok:'#333',youtube:'#e00'};

if (TS.length) {
  new Chart(document.getElementById('tsChart'), {
    type:'line',
    data:{ labels:TS.map(r=>r.period), datasets:[{
      label:'Engagement', data:TS.map(r=>(r.likes||0)+(r.comments||0)+(r.shares||0)+(r.saves||0)),
      borderColor:'#65a1d8',backgroundColor:'rgba(101,161,216,.12)',borderWidth:2.5,fill:true,tension:.4,pointRadius:2
    }]},
    options:{ responsive:true,maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{x:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}},
              y:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}}} }
  });
}
if (PL.length) {
  new Chart(document.getElementById('platformChart'), {
    type:'doughnut',
    data:{ labels:PL.map(p=>p.platform),
      datasets:[{data:PL.map(p=>p.engagement),backgroundColor:PL.map(p=>COLORS[p.platform?.toLowerCase()]||'#aaa'),borderWidth:2}]},
    options:{ responsive:true,maintainAspectRatio:false,cutout:'65%',
      plugins:{legend:{position:'bottom',labels:{font:{size:11}}}} }
  });
}
</script>
@endpush
