@extends('layouts.backend')
@section('title', 'Reach & Impressions')

@section('content')

<div class="mb-4">
  <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Analytics</div>
  <h1 class="text-3xl font-extrabold tracking-tight text-ink">Reach &amp; Impressions</h1>
</div>

@include('backend.analytics._nav')
@include('backend.analytics._filter_bar')

@php $kpi = $data['kpi'] ?? []; @endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Total Reach</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['reach'] ?? 0) }}</div>
    <div class="text-xs text-ink/50 mt-1">Unique accounts reached</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Impressions</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['impressions'] ?? 0) }}</div>
    <div class="text-xs text-ink/50 mt-1">Total content views</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Frequency</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['frequency'] ?? 0, 2) }}×</div>
    <div class="text-xs text-ink/50 mt-1">Avg impressions per person</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">CTR</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['ctr'] ?? 0, 2) }}%</div>
    <div class="text-xs text-ink/50 mt-1">Click-through rate</div>
  </div>
</div>

<div class="grid lg:grid-cols-[1.7fr_1fr] gap-4 mb-6">
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">Reach &amp; Impressions over time</h3>
    <p class="text-xs text-ink/50 mb-4">Daily breakdown</p>
    <canvas id="tsChart" style="height:260px"></canvas>
  </div>

  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">Reach by platform</h3>
    <p class="text-xs text-ink/50 mb-4">Share of total reach</p>
    <canvas id="platformChart" style="height:200px"></canvas>
    <div class="mt-4 space-y-2">
      @foreach($data['by_platform'] ?? [] as $p)
      @php $totalReach = array_sum(array_column($data['by_platform'], 'reach')) ?: 1; @endphp
      <div class="flex items-center gap-2 text-sm">
        <span class="flex-1 capitalize text-ink/80">{{ $p['platform'] }}</span>
        <span class="font-bold">{{ number_format($p['reach']) }}</span>
        <span class="text-ink/40 text-xs">{{ round($p['reach'] / $totalReach * 100, 1) }}%</span>
      </div>
      @endforeach
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const TS = @json($data['time_series'] ?? []);
const PL = @json($data['by_platform'] ?? []);
const COLORS = {instagram:'#65a1d8',facebook:'#021b2e',twitter:'#2f76bd',linkedin:'#8bb4dc',tiktok:'#333',youtube:'#e00'};

if (TS.length) {
  new Chart(document.getElementById('tsChart'), {
    type:'bar',
    data:{ labels:TS.map(r=>r.period), datasets:[
      { label:'Reach',       data:TS.map(r=>r.reach||0),       backgroundColor:'rgba(101,161,216,.7)', yAxisID:'y' },
      { label:'Impressions', data:TS.map(r=>r.impressions||0), backgroundColor:'rgba(2,27,46,.2)',     yAxisID:'y' },
    ]},
    options:{ responsive:true,maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{font:{size:11}}}},
      scales:{x:{stacked:false,grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}},
              y:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}}} }
  });
}
const plEl = document.getElementById('platformChart');
if (plEl && PL.length) {
  new Chart(plEl, {
    type:'doughnut',
    data:{ labels:PL.map(p=>p.platform), datasets:[{
      data:PL.map(p=>p.reach),
      backgroundColor:PL.map(p=>COLORS[p.platform?.toLowerCase()]||'#aaa'),borderWidth:2,borderColor:'#fff'
    }]},
    options:{ responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:false}} }
  });
}
</script>
@endpush
