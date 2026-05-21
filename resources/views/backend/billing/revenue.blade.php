@extends('layouts.backend')

@section('title', 'Revenue Dashboard')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endsection

@section('content')
<div class="mb-6">
  <h1 class="text-xl font-bold text-ink">Revenue Dashboard</h1>
  <p class="text-sm text-ink/50 mt-0.5">MRR, ARR, churn, and payment health</p>
</div>

{{-- KPI Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  {{-- MRR --}}
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">MRR</div>
    <div class="text-2xl font-black text-ink">${{ number_format($summary['mrr'] / 100, 0) }}</div>
    <div class="text-xs text-ink/40 mt-1">Monthly recurring revenue</div>
  </div>

  {{-- ARR --}}
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">ARR</div>
    <div class="text-2xl font-black text-ink">${{ number_format($summary['arr'] / 100, 0) }}</div>
    <div class="text-xs text-ink/40 mt-1">Annual run rate</div>
  </div>

  {{-- Active Subs --}}
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">Active</div>
    <div class="text-2xl font-black text-ink">{{ number_format($summary['active_count']) }}</div>
    <div class="text-xs text-ink/40 mt-1">Active subscriptions</div>
  </div>

  {{-- Churn --}}
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">Churn (30d)</div>
    <div class="text-2xl font-black {{ $summary['churn_rate'] > 5 ? 'text-coral' : 'text-ink' }}">
      {{ number_format($summary['churn_rate'], 1) }}%
    </div>
    <div class="text-xs text-ink/40 mt-1">Cancellation rate</div>
  </div>
</div>

{{-- Second Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">New (30d)</div>
    <div class="text-2xl font-black text-mint">{{ $summary['new_subscriptions'] }}</div>
    <div class="text-xs text-ink/40 mt-1">New subscriptions</div>
  </div>

  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">Revenue (30d)</div>
    <div class="text-2xl font-black text-ink">${{ number_format($summary['total_revenue'] / 100, 0) }}</div>
    <div class="text-xs text-ink/40 mt-1">Collected payments</div>
  </div>

  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">Success Rate</div>
    <div class="text-2xl font-black {{ $summary['payment_success_rate'] < 90 ? 'text-coral' : 'text-mint' }}">
      {{ number_format($summary['payment_success_rate'], 1) }}%
    </div>
    <div class="text-xs text-ink/40 mt-1">Payment success rate</div>
  </div>

  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">Failed (30d)</div>
    <div class="text-2xl font-black {{ $summary['failed_payments'] > 0 ? 'text-coral' : 'text-ink' }}">
      {{ $summary['failed_payments'] }}
    </div>
    <div class="text-xs text-ink/40 mt-1">Failed payments</div>
  </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

  {{-- Revenue by Month --}}
  <div class="card p-5 lg:col-span-2">
    <div class="flex items-center justify-between mb-4">
      <div class="font-bold text-sm text-ink">Monthly Revenue (12 months)</div>
    </div>
    <canvas id="revenueChart" height="90"></canvas>
  </div>

  {{-- Subs by Plan --}}
  <div class="card p-5">
    <div class="font-bold text-sm text-ink mb-4">Subscriptions by Plan</div>
    <canvas id="planChart" height="160"></canvas>
    <div class="mt-4 space-y-2">
      @foreach ($byPlan as $row)
      <div class="flex items-center justify-between text-xs">
        <span class="text-ink/60 font-medium">{{ $row->name ?? 'Unknown' }}</span>
        <span class="font-bold text-ink">{{ $row->total }}</span>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const revenueData = @json($revenueByMonth);

new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: revenueData.map(r => r.month),
    datasets: [{
      label: 'Revenue',
      data: revenueData.map(r => r.revenue / 100),
      backgroundColor: 'rgba(101,161,216,.25)',
      borderColor: '#4a8ccc',
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        ticks: { callback: v => '$' + v.toLocaleString() },
        grid: { color: 'rgba(2,27,46,.05)' }
      },
      x: { grid: { display: false } }
    }
  }
});

const planData = @json($byPlan);
new Chart(document.getElementById('planChart'), {
  type: 'doughnut',
  data: {
    labels: planData.map(r => r.name ?? 'Unknown'),
    datasets: [{
      data: planData.map(r => r.total),
      backgroundColor: ['#4a8ccc','#22B07E','#FDBB1F','#FF401C','#8bb4dc'],
      borderWidth: 0,
    }]
  },
  options: {
    responsive: true,
    cutout: '70%',
    plugins: { legend: { display: false } }
  }
});
</script>
@endsection
