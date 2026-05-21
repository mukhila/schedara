@extends('admin.layouts.admin')

@section('title', 'Analytics Overview')
@section('heading', 'Analytics Overview')
@section('subheading', 'Platform-wide metrics and growth trends')

@section('content')

{{-- KPI Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $kpis = [
            ['label' => 'Total Users',      'value' => number_format($stats['total_users']),     'change' => '+' . $stats['new_users_today'] . ' today',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => '#7c3aed'],
            ['label' => 'Active Tenants',   'value' => number_format($stats['active_tenants']),  'change' => $stats['total_tenants'] . ' total',            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => '#2563eb'],
            ['label' => 'Open Tickets',     'value' => number_format($stats['open_tickets']),    'change' => round(($ticketStats['avg_response_minutes'] ?? 0) / 60, 1) . 'h avg response', 'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', 'color' => '#d97706'],
            ['label' => 'Failed Jobs',      'value' => number_format($stats['failed_jobs']),     'change' => 'in queue',                                    'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                                             'color' => ($stats['failed_jobs'] > 0 ? '#dc2626' : '#059669')],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $kpi['label'] }}</p>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $kpi['color'] }}18">
                <svg class="w-4 h-4" style="color:{{ $kpi['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $kpi['icon'] }}"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $kpi['value'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $kpi['change'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- User Growth --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">User Growth (12 months)</h3>
            <span class="text-xs text-gray-400">Monthly new registrations</span>
        </div>
        <canvas id="userGrowthChart" height="180"></canvas>
    </div>

    {{-- Tenant Growth --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Workspace Growth (12 months)</h3>
            <span class="text-xs text-gray-400">Monthly new workspaces</span>
        </div>
        <canvas id="tenantGrowthChart" height="180"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Daily signups (30d) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Daily Signups — Last 30 Days</h3>
        <canvas id="dailySignupsChart" height="130"></canvas>
    </div>

    {{-- Ticket breakdown --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Ticket Summary</h3>
        <div class="space-y-3 mt-2">
            @php
                $ticketRows = [
                    ['label' => 'Total Tickets',     'value' => $ticketStats['total'] ?? 0,          'color' => 'bg-gray-100 text-gray-700'],
                    ['label' => 'Open / In Progress','value' => $ticketStats['open_count'] ?? 0,     'color' => 'bg-blue-100 text-blue-700'],
                    ['label' => 'Resolved',          'value' => $ticketStats['resolved_count'] ?? 0, 'color' => 'bg-emerald-100 text-emerald-700'],
                    ['label' => 'Critical Priority', 'value' => $ticketStats['critical_count'] ?? 0, 'color' => 'bg-red-100 text-red-700'],
                ];
            @endphp
            @foreach($ticketRows as $row)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-600">{{ $row['label'] }}</span>
                <span class="text-sm font-semibold px-2 py-0.5 rounded-lg {{ $row['color'] }}">{{ number_format($row['value']) }}</span>
            </div>
            @endforeach

            @if(!empty($failedJobsByQueue))
            <div class="pt-3">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Failed Jobs by Queue</p>
                @foreach($failedJobsByQueue as $queue => $count)
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-xs text-gray-500 font-mono">{{ $queue }}</span>
                    <span class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const chartDefaults = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
        y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af', stepSize: 1 } },
    },
};

new Chart(document.getElementById('userGrowthChart'), {
    type: 'bar',
    data: {
        labels: @json(array_keys($userGrowth)),
        datasets: [{ data: @json(array_values($userGrowth)), backgroundColor: '#7c3aed', borderRadius: 5, borderSkipped: false }],
    },
    options: { ...chartDefaults, plugins: { ...chartDefaults.plugins, tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} new users` } } } },
});

new Chart(document.getElementById('tenantGrowthChart'), {
    type: 'bar',
    data: {
        labels: @json(array_keys($tenantGrowth)),
        datasets: [{ data: @json(array_values($tenantGrowth)), backgroundColor: '#2563eb', borderRadius: 5, borderSkipped: false }],
    },
    options: { ...chartDefaults, plugins: { ...chartDefaults.plugins, tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} new workspaces` } } } },
});

new Chart(document.getElementById('dailySignupsChart'), {
    type: 'line',
    data: {
        labels: @json(array_keys($usersByDay)),
        datasets: [{
            data: @json(array_values($usersByDay)),
            borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.08)',
            fill: true, tension: 0.4, pointRadius: 3, pointHoverRadius: 5,
            pointBackgroundColor: '#7c3aed',
        }],
    },
    options: {
        ...chartDefaults,
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9ca3af', maxTicksLimit: 10 } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af', stepSize: 1 } },
        },
    },
});
</script>
@endpush
@endsection
