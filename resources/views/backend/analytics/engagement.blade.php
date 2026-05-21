@extends('layouts.backend')
@section('title', 'Engagement Analytics')

@section('content')

<div class="mb-4">
  <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Analytics</div>
  <h1 class="text-3xl font-extrabold tracking-tight text-ink">Engagement</h1>
</div>

@include('backend.analytics._nav')
@include('backend.analytics._filter_bar')

{{-- KPI row --}}
@php
  $kpi = $data['kpi'] ?? [];
  $total = ($kpi['total_likes']??0)+($kpi['total_comments']??0)+($kpi['total_shares']??0)+($kpi['total_saves']??0);
  $breakdown = $data['breakdown'] ?? [];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Total Engagement</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($total) }}</div>
    <div class="text-xs text-ink/50 mt-1">{{ number_format($kpi['avg_engagement_rate']??0, 2) }}% avg rate</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Likes</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['total_likes']??0) }}</div>
    <div class="text-xs text-ink/50 mt-1">{{ $breakdown['likes']['percentage']??0 }}% of engagement</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Comments</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['total_comments']??0) }}</div>
    <div class="text-xs text-ink/50 mt-1">{{ $breakdown['comments']['percentage']??0 }}% of engagement</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Shares & Saves</div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format(($kpi['total_shares']??0)+($kpi['total_saves']??0)) }}</div>
    <div class="text-xs text-ink/50 mt-1">High-intent actions</div>
  </div>
</div>

<div class="grid lg:grid-cols-[1.7fr_1fr] gap-4 mb-6">
  {{-- Time series --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">Engagement over time</h3>
    <p class="text-xs text-ink/50 mb-4">Likes + comments + shares + saves per day</p>
    <canvas id="tsChart" style="height:260px"></canvas>
  </div>

  {{-- Breakdown pie --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-1">Engagement breakdown</h3>
    <p class="text-xs text-ink/50 mb-4">Type distribution</p>
    @if($total > 0)
    <canvas id="breakdownChart" style="height:200px"></canvas>
    <div class="mt-4 space-y-2">
      @foreach($breakdown as $type => $bd)
      <div class="flex items-center gap-2 text-sm">
        <span class="w-2.5 h-2.5 rounded-sm inline-block flex-shrink-0"
          style="background:{{ ['likes'=>'#65a1d8','comments'=>'#2f76bd','shares'=>'#021b2e','saves'=>'#8bb4dc'][$type] ?? '#aaa' }}"></span>
        <span class="flex-1 capitalize text-ink/70">{{ $type }}</span>
        <span class="font-bold">{{ number_format($bd['count']) }}</span>
        <span class="text-ink/40 text-xs w-10 text-right">{{ $bd['percentage'] }}%</span>
      </div>
      @endforeach
    </div>
    @else
    <div class="h-48 flex items-center justify-center text-ink/30 text-sm">No engagement data</div>
    @endif
  </div>
</div>

{{-- Platform breakdown --}}
<div class="card p-6 mb-6">
  <h3 class="text-lg font-bold mb-4">Engagement by platform</h3>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-[10px] font-bold uppercase tracking-wider text-ink/40">
          <th class="py-2 text-left">Platform</th>
          <th class="py-2 text-right">Posts</th>
          <th class="py-2 text-right">Reach</th>
          <th class="py-2 text-right">Likes</th>
          <th class="py-2 text-right">Comments</th>
          <th class="py-2 text-right">Shares</th>
          <th class="py-2 text-right">Eng. Rate</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        @forelse($data['by_platform'] ?? [] as $p)
        <tr class="hover:bg-paper/80 transition-colors">
          <td class="py-3 font-bold capitalize">{{ $p['platform'] }}</td>
          <td class="py-3 text-right">{{ number_format($p['posts']) }}</td>
          <td class="py-3 text-right">{{ number_format($p['reach']) }}</td>
          <td class="py-3 text-right">—</td>
          <td class="py-3 text-right">—</td>
          <td class="py-3 text-right">{{ number_format($p['engagement']) }}</td>
          <td class="py-3 text-right">
            <span class="pill {{ $p['engagement_rate'] >= 3 ? 'pill-mint' : 'pill-gold' }}">{{ number_format($p['engagement_rate'], 2) }}%</span>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="py-8 text-center text-ink/40 text-sm">No data for this period</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const TS = @json($data['time_series'] ?? []);
const BD = @json($data['breakdown'] ?? {});

if (TS.length) {
  new Chart(document.getElementById('tsChart'), {
    type:'line',
    data:{ labels:TS.map(r=>r.period), datasets:[
      { label:'Likes',    data:TS.map(r=>r.likes||0),    borderColor:'#65a1d8',fill:false,tension:.4,borderWidth:2,pointRadius:1 },
      { label:'Comments', data:TS.map(r=>r.comments||0), borderColor:'#2f76bd',fill:false,tension:.4,borderWidth:2,pointRadius:1 },
      { label:'Shares',   data:TS.map(r=>r.shares||0),   borderColor:'#021b2e',fill:false,tension:.4,borderWidth:2,pointRadius:1 },
      { label:'Saves',    data:TS.map(r=>r.saves||0),    borderColor:'#8bb4dc',fill:false,tension:.4,borderWidth:2,pointRadius:1 },
    ]},
    options:{ responsive:true,maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{font:{size:11}}}},
      scales:{x:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}},
              y:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}}} }
  });
}
const bKeys = Object.keys(BD);
if (bKeys.length) {
  new Chart(document.getElementById('breakdownChart'), {
    type:'doughnut',
    data:{
      labels:bKeys.map(k=>k.charAt(0).toUpperCase()+k.slice(1)),
      datasets:[{ data:bKeys.map(k=>BD[k].count),
        backgroundColor:['#65a1d8','#2f76bd','#021b2e','#8bb4dc'],borderWidth:2,borderColor:'#fff' }]
    },
    options:{ responsive:true,maintainAspectRatio:false,cutout:'60%',
      plugins:{legend:{display:false}} }
  });
}
</script>
@endpush
