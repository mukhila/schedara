@extends('admin.layouts.admin')

@section('title', 'Subscriptions')
@section('heading', 'Subscription Monitor')

@section('content')
{{-- Summary KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $kpis = [
            ['label' => 'Active',       'value' => $summary['active_subscriptions'] ?? 0],
            ['label' => 'New (30d)',     'value' => $summary['new_subscriptions_30d'] ?? 0],
            ['label' => 'Revenue (30d)', 'value' => '$' . number_format(($summary['total_revenue_30d'] ?? 0) / 100, 2)],
            ['label' => 'Churn Rate',   'value' => ($summary['churn_rate'] ?? 0) . '%'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $kpi['label'] }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $kpi['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search workspace…"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
        </div>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            <option value="">All Status</option>
            @foreach(['active','trialing','cancelled','paused','past_due'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">Filter</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Workspace</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Plan</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Interval</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Renews</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($subscriptions as $sub)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5 font-medium text-gray-900">{{ $sub->tenant?->name ?? '—' }}</td>
                <td class="px-4 py-3.5 text-gray-700">{{ $sub->plan?->name ?? '—' }}</td>
                <td class="px-4 py-3.5">
                    @php $colors = ['active'=>'emerald','trialing'=>'blue','cancelled'=>'red','paused'=>'amber','past_due'=>'orange']; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $colors[$sub->status] ?? 'gray' }}-100 text-{{ $colors[$sub->status] ?? 'gray' }}-700">
                        {{ ucfirst($sub->status) }}
                    </span>
                </td>
                <td class="px-4 py-3.5 text-gray-600 capitalize">{{ $sub->interval }}</td>
                <td class="px-4 py-3.5 text-gray-500 text-xs">{{ $sub->cancel_at?->format('M d, Y') ?? '—' }}</td>
                <td class="px-5 py-3.5 text-right space-x-2">
                    <a href="{{ route('admin.subscriptions.show', $sub) }}" class="text-sm text-violet-600 hover:underline">View</a>
                    @if(in_array($sub->status, ['active','trialing']))
                    <form method="POST" action="{{ route('admin.subscriptions.cancel', $sub) }}" class="inline"
                          onsubmit="return confirm('Cancel this subscription?')">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:underline">Cancel</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No subscriptions found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($subscriptions->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $subscriptions->links() }}</div>
    @endif
</div>
@endsection
