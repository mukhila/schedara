@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Platform Dashboard')

@section('content')
{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $kpis = [
            ['label' => 'Total Users',       'value' => number_format($stats['total_users']),     'sub' => '+' . $stats['new_users_today'] . ' today',   'color' => 'violet'],
            ['label' => 'Active Tenants',    'value' => number_format($stats['active_tenants']),   'sub' => $stats['total_tenants'] . ' total',           'color' => 'blue'],
            ['label' => 'MRR',               'value' => '$' . number_format($stats['mrr'] / 100, 2), 'sub' => 'ARR $' . number_format($stats['arr'] / 100, 0), 'color' => 'emerald'],
            ['label' => 'Open Tickets',      'value' => number_format($stats['open_tickets']),     'sub' => $stats['failed_jobs'] . ' failed jobs',       'color' => 'amber'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $kpi['label'] }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $kpi['value'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $kpi['sub'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- User Growth Chart --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">User & Tenant Growth</h3>
        <canvas id="growthChart" height="120"></canvas>
    </div>

    {{-- Subscription Stats --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Subscriptions</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Active</span>
                <span class="font-semibold text-gray-900">{{ number_format($stats['active_subs']) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Churn Rate</span>
                <span class="font-semibold text-{{ $stats['churn_rate'] > 5 ? 'red' : 'emerald' }}-600">{{ $stats['churn_rate'] }}%</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Revenue (30d)</span>
                <span class="font-semibold text-gray-900">${{ number_format(($stats['total_revenue'] ?? 0) / 100, 2) }}</span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.subscriptions.index') }}" class="text-sm text-violet-600 hover:underline">View all subscriptions →</a>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-900">Recent Activity</h3>
        <a href="{{ route('admin.activity.index') }}" class="text-sm text-violet-600 hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left py-2 pr-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Admin</th>
                    <th class="text-left py-2 pr-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Action</th>
                    <th class="text-left py-2 pr-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Module</th>
                    <th class="text-left py-2 font-medium text-gray-500 text-xs uppercase tracking-wide">Description</th>
                    <th class="text-right py-2 font-medium text-gray-500 text-xs uppercase tracking-wide">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentActivity as $log)
                <tr>
                    <td class="py-2.5 pr-4 text-gray-900">{{ $log->admin?->name ?? 'System' }}</td>
                    <td class="py-2.5 pr-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-700">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="py-2.5 pr-4 text-gray-500">{{ $log->module }}</td>
                    <td class="py-2.5 text-gray-700 max-w-xs truncate">{{ $log->description }}</td>
                    <td class="py-2.5 text-right text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-gray-400">No activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const labels = @json(array_keys($userGrowth));
const userData = @json(array_values($userGrowth));
const tenantData = @json(array_values($tenantGrowth));

new Chart(document.getElementById('growthChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'New Users',   data: userData,   backgroundColor: '#7c3aed', borderRadius: 4 },
            { label: 'New Tenants', data: tenantData, backgroundColor: '#a78bfa', borderRadius: 4 },
        ],
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } },
});
</script>
@endsection
