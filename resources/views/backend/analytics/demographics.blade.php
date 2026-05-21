@extends('layouts.backend')
@section('title', 'Audience Demographics')

@section('content')

<div class="mb-4">
  <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Analytics</div>
  <h1 class="text-3xl font-extrabold tracking-tight text-ink">Demographics</h1>
</div>

@include('backend.analytics._nav')
@include('backend.analytics._filter_bar')

@php
  $age     = $data['age']     ?? [];
  $gender  = $data['gender']  ?? [];
  $country = $data['country'] ?? [];
  $city    = $data['city']    ?? [];
  $device  = $data['device']  ?? [];
@endphp

<div class="grid lg:grid-cols-2 gap-4 mb-6">

  {{-- Age --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-4">Age distribution</h3>
    @if(count($age) > 0)
    <canvas id="ageChart" style="height:200px"></canvas>
    @else
    <div class="h-40 flex items-center justify-center text-ink/30 text-sm">No age data</div>
    @endif
  </div>

  {{-- Gender --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-4">Gender distribution</h3>
    @if(count($gender) > 0)
    <canvas id="genderChart" style="height:200px"></canvas>
    <div class="mt-4 space-y-2">
      @foreach($gender as $g)
      <div class="flex items-center gap-3 text-sm">
        <div class="flex-1 h-2 bg-line rounded-full overflow-hidden">
          <div class="h-full bg-brand-400 rounded-full" style="width:{{ $g['percentage'] ?? 0 }}%"></div>
        </div>
        <span class="w-24 capitalize font-medium">{{ $g['dimension_value'] }}</span>
        <span class="font-bold w-12 text-right">{{ number_format($g['percentage'] ?? 0, 1) }}%</span>
      </div>
      @endforeach
    </div>
    @else
    <div class="h-40 flex items-center justify-center text-ink/30 text-sm">No gender data</div>
    @endif
  </div>

  {{-- Top countries --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-4">Top countries</h3>
    @if(count($country) > 0)
    <div class="space-y-3">
      @foreach(array_slice($country, 0, 8) as $c)
      <div>
        <div class="flex justify-between text-sm mb-1">
          <span class="font-medium text-ink/80">{{ $c['dimension_value'] }}</span>
          <span class="font-bold">{{ number_format($c['count']) }}</span>
        </div>
        <div class="h-1.5 bg-line rounded-full overflow-hidden">
          <div class="h-full bg-brand-400 rounded-full" style="width:{{ $c['percentage'] ?? 0 }}%"></div>
        </div>
      </div>
      @endforeach
    </div>
    @else
    <div class="h-40 flex items-center justify-center text-ink/30 text-sm">No country data</div>
    @endif
  </div>

  {{-- Device --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold mb-4">Device breakdown</h3>
    @if(count($device) > 0)
    <canvas id="deviceChart" style="height:200px"></canvas>
    <div class="mt-4 space-y-2">
      @foreach($device as $d)
      <div class="flex items-center gap-2 text-sm">
        <span class="flex-1 capitalize text-ink/80">{{ $d['dimension_value'] }}</span>
        <span class="font-bold">{{ number_format($d['percentage'] ?? 0, 1) }}%</span>
      </div>
      @endforeach
    </div>
    @else
    <div class="h-40 flex items-center justify-center text-ink/30 text-sm">No device data</div>
    @endif
  </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const AGE    = @json($age);
const GENDER = @json($gender);
const DEVICE = @json($device);

const brandColors = ['#65a1d8','#2f76bd','#021b2e','#8bb4dc','#dceaf5','#4a8ccc','#235b95'];

if (AGE.length) {
  new Chart(document.getElementById('ageChart'), {
    type:'bar',
    data:{ labels:AGE.map(r=>r.dimension_value), datasets:[{
      label:'Users', data:AGE.map(r=>r.count), backgroundColor:brandColors, borderRadius:4
    }]},
    options:{ responsive:true,maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{x:{grid:{display:false},ticks:{color:'#021b2e66',font:{size:11}}},
              y:{grid:{color:'#e3e9ee'},ticks:{color:'#021b2e66',font:{size:11}}}} }
  });
}
if (GENDER.length) {
  new Chart(document.getElementById('genderChart'), {
    type:'doughnut',
    data:{ labels:GENDER.map(g=>g.dimension_value), datasets:[{
      data:GENDER.map(g=>g.count), backgroundColor:brandColors, borderWidth:2, borderColor:'#fff'
    }]},
    options:{ responsive:true,maintainAspectRatio:false,cutout:'60%',
      plugins:{legend:{position:'bottom',labels:{font:{size:11}}}} }
  });
}
if (DEVICE.length) {
  new Chart(document.getElementById('deviceChart'), {
    type:'doughnut',
    data:{ labels:DEVICE.map(d=>d.dimension_value), datasets:[{
      data:DEVICE.map(d=>d.count), backgroundColor:brandColors, borderWidth:2, borderColor:'#fff'
    }]},
    options:{ responsive:true,maintainAspectRatio:false,cutout:'60%',
      plugins:{legend:{position:'bottom',labels:{font:{size:11}}}} }
  });
}
</script>
@endpush
