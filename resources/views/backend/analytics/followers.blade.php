@extends('layouts.backend')
@section('title', 'Follower Analytics')

@section('content')

<div class="mb-4">
  <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Analytics</div>
  <h1 class="text-3xl font-extrabold tracking-tight text-ink">Follower Growth</h1>
</div>

@include('backend.analytics._nav')
@include('backend.analytics._filter_bar')

@php
  $kpi      = $data['kpi'] ?? [];
  $growthSign = ($kpi['net_growth'] ?? 0) >= 0 ? '+' : '';
@endphp

{{-- KPI row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Total Followers</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['total_followers'] ?? 0) }}</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Net Growth</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight {{ ($kpi['net_growth'] ?? 0) >= 0 ? 'text-mint' : 'text-coral' }}">
      {{ $growthSign }}{{ number_format($kpi['net_growth'] ?? 0) }}
    </div>
    <div class="text-xs text-ink/50 mt-1">This period</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Growth Rate</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight {{ ($kpi['growth_rate'] ?? 0) >= 0 ? 'text-mint' : 'text-coral' }}">
      {{ $growthSign }}{{ number_format($kpi['growth_rate'] ?? 0, 2) }}%
    </div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Unfollows</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight text-coral">{{ number_format($kpi['total_unfollows'] ?? 0) }}</div>
    <div class="text-xs text-ink/50 mt-1">This period</div>
  </div>
</div>

{{-- Charts --}}
<div class="grid lg:grid-cols-[1.7fr_1fr] gap-4 mb-6">
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">Follower growth over time</h3>
    <p class="text-xs text-ink/50 mb-4">Total followers per day</p>
    <canvas id="growthChart" style="height:260px"></canvas>
  </div>

  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">By platform</h3>
    <p class="text-xs text-ink/50 mb-4">Follower distribution</p>
    @if(count($data['by_platform'] ?? []) > 0)
    <canvas id="platformChart" style="height:200px"></canvas>
    <div class="mt-4 space-y-2.5">
      @foreach($data['by_platform'] as $p)
      <div class="flex items-center gap-2 text-sm">
        <span class="flex-1 capitalize text-ink/80">{{ $p['platform'] }}</span>
        <span class="font-bold">{{ number_format($p['followers']) }}</span>
        <span class="text-ink/40 text-xs">−{{ number_format($p['unfollows']) }}</span>
      </div>
      @endforeach
    </div>
    @else
    <div class="h-40 flex items-center justify-center text-ink/30 text-sm">No platform data</div>
    @endif
  </div>
</div>

{{-- Best day --}}
@if(!empty($data['best_day']))
<div class="card p-6 mb-6">
  <h3 class="text-lg font-bold mb-2">Best growth day</h3>
  <div class="flex items-center gap-6">
    <div class="text-4xl font-extrabold text-brand-600">{{ $data['best_day']['date'] ?? '—' }}</div>
    <div>
      <div class="text-sm text-ink/60">Peak followers: <span class="font-bold text-ink">{{ number_format($data['best_day']['followers'] ?? 0) }}</span></div>
    </div>
  </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const TS = @json($data['time_series'] ?? []);
const PL = @json($data['by_platform'] ?? []);
const COLORS = {instagram:'#65a1d8',facebook:'#021b2e',twitter:'#2f76bd',linkedin:'#8bb4dc',tiktok:'#333',youtube:'#e00'};

if (TS.length) {
  new Chart(document.getElementById('growthChart'), {
    type:'line',
    data:{ labels:TS.map(r=>r.date), datasets:[
      { label:'Followers', data:TS.map(r=>r.followers||0), borderColor:'#65a1d8', backgroundColor:'rgba(101,161,216,.12)', fill:true, tension:.4, borderWidth:2.5, pointRadius:2 },
      { label:'Unfollows', data:TS.map(r=>r.unfollows||0), borderColor:'#FF401C', fill:false, tension:.4, borderWidth:1.5, borderDash:[4,4], pointRadius:0 },
    ]},
    options:{ responsive:true,maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{font:{size:11}}}},
      scales:{x:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}},
              y:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}}} }
  });
}

const plEl = document.getElementById('platformChart');
if (plEl && PL.length) {
  new Chart(plEl, {
    type:'doughnut',
    data:{ labels:PL.map(p=>p.platform), datasets:[{
      data:PL.map(p=>p.followers),
      backgroundColor:PL.map(p=>COLORS[p.platform?.toLowerCase()]||'#aaa'),
      borderWidth:2,borderColor:'#fff'
    }]},
    options:{ responsive:true,maintainAspectRatio:false,cutout:'65%',
      plugins:{legend:{display:false}} }
  });
}
</script>
@endpush
