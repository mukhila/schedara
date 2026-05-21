@extends('admin.layouts.admin')

@section('title', 'Revenue Management')
@section('heading', 'Revenue Management')
@section('subheading', 'MRR, ARR, churn, and subscription analytics')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['label' => 'MRR',                'value' => '$' . number_format(($summary['mrr'] ?? 0) / 100, 2),    'sub' => 'Monthly Recurring Revenue', 'accent' => '#7c3aed'],
            ['label' => 'ARR',                'value' => '$' . number_format(($summary['arr'] ?? 0) / 100, 2),    'sub' => 'Annual Recurring Revenue',  'accent' => '#2563eb'],
            ['label' => 'Active Subs',        'value' => number_format($summary['active_subscriptions'] ?? 0),    'sub' => 'Paying customers',           'accent' => '#059669'],
            ['label' => 'Churn Rate',         'value' => ($summary['churn_rate'] ?? 0) . '%',                     'sub' => 'Monthly churn',              'accent' => ($summary['churn_rate'] ?? 0) > 5 ? '#dc2626' : '#d97706'],
        ];
    @endphp
    @foreach($cards as $card)
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $card['label'] }}</p>
            <div class="w-2 h-2 rounded-full" style="background:{{ $card['accent'] }}"></div>
        </div>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $card['value'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Secondary KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $secondary = [
            ['label' => 'New Subs (30d)',      'value' => number_format($summary['new_subscriptions_30d'] ?? 0)],
            ['label' => 'Revenue (30d)',        'value' => '$' . number_format(($summary['total_revenue_30d'] ?? 0) / 100, 2)],
            ['label' => 'Payment Success',     'value' => ($summary['payment_success_rate'] ?? 0) . '%'],
            ['label' => 'Failed Payments',     'value' => number_format($summary['failed_payments_30d'] ?? 0)],
        ];
    @endphp
    @foreach($secondary as $s)
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $s['label'] }}</p>
        <p class="text-lg font-bold text-gray-900 mt-1">{{ $s['value'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Monthly Revenue Chart --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Monthly Revenue (12 months)</h3>
            <span class="text-xs text-gray-400">in cents</span>
        </div>
        <canvas id="revenueChart" height="200"></canvas>
    </div>

    {{-- Plans Breakdown --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Subscriptions by Plan</h3>
        <canvas id="plansChart" height="200"></canvas>
        <div class="mt-4 space-y-2">
            @php
                $planColors = ['#7c3aed','#2563eb','#059669','#d97706','#dc2626','#0891b2'];
                $planLabels = array_keys($byPlan);
                $planValues = array_values($byPlan);
            @endphp
            @foreach($planLabels as $i => $label)
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-sm flex-shrink-0" style="background:{{ $planColors[$i % count($planColors)] }}"></div>
                    <span class="text-gray-700 truncate max-w-[130px]">{{ $label }}</span>
                </div>
                <span class="font-semibold text-gray-900">{{ $planValues[$i] }}</span>
            </div>
            @endforeach
            @if(empty($planLabels))
            <p class="text-gray-400 text-sm text-center py-4">No subscription data yet.</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const months  = @json(array_keys($revenueByMonth));
const amounts = @json(array_values($revenueByMonth));

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Revenue',
            data: amounts,
            backgroundColor: amounts.map((v, i) => i === amounts.length - 1 ? '#7c3aed' : '#ddd6fe'),
            borderRadius: 6,
            borderSkipped: false,
        }],
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` $${(ctx.parsed.y / 100).toFixed(2)}` } },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af', callback: v => '$' + (v/100).toFixed(0) } },
        },
    },
});

@if(!empty($planLabels))
new Chart(document.getElementById('plansChart'), {
    type: 'doughnut',
    data: {
        labels: @json($planLabels),
        datasets: [{
            data: @json($planValues),
            backgroundColor: ['#7c3aed','#2563eb','#059669','#d97706','#dc2626','#0891b2'],
            borderWidth: 0,
            hoverOffset: 4,
        }],
    },
    options: {
        responsive: true,
        cutout: '70%',
        plugins: { legend: { display: false } },
    },
});
@endif
</script>
@endpush
@endsection
