@extends('admin.layouts.admin')

@section('title', 'Support Tickets')
@section('heading', 'Support Tickets')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @php
        $tStats = [
            ['label' => 'Total',    'value' => $stats['total'] ?? 0],
            ['label' => 'Open',     'value' => $stats['open_count'] ?? 0],
            ['label' => 'Resolved', 'value' => $stats['resolved_count'] ?? 0],
            ['label' => 'Critical', 'value' => $stats['critical_count'] ?? 0],
            ['label' => 'Avg Response', 'value' => round(($stats['avg_response_minutes'] ?? 0) / 60, 1) . 'h'],
        ];
    @endphp
    @foreach($tStats as $ts)
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $ts['label'] }}</p>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ $ts['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ticket…"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
        </div>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Status</option>
            @foreach(['open','in_progress','waiting','resolved','closed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="priority" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Priority</option>
            @foreach(['critical','high','medium','low'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <select name="assigned_to" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Assignees</option>
            @foreach($admins as $admin)
                <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">Filter</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Ticket</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">User</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Priority</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Assignee</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Created</th>
                <th class="text-right px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tickets as $ticket)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5">
                    <p class="font-medium text-gray-900 truncate max-w-xs">{{ $ticket->subject }}</p>
                    <p class="text-xs text-gray-400">{{ $ticket->ticket_number }}</p>
                </td>
                <td class="px-4 py-3.5 text-gray-600">{{ $ticket->user?->name ?? '—' }}</td>
                <td class="px-4 py-3.5">
                    @php $pc = ['critical'=>'red','high'=>'amber','medium'=>'blue','low'=>'gray']; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $pc[$ticket->priority] ?? 'gray' }}-100 text-{{ $pc[$ticket->priority] ?? 'gray' }}-700">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </td>
                <td class="px-4 py-3.5">
                    @php $sc = ['open'=>'blue','in_progress'=>'violet','waiting'=>'amber','resolved'=>'emerald','closed'=>'gray']; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc[$ticket->status] ?? 'gray' }}-100 text-{{ $sc[$ticket->status] ?? 'gray' }}-700">
                        {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                    </span>
                </td>
                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                <td class="px-4 py-3.5 text-gray-400 text-xs">{{ $ticket->created_at->format('M d') }}</td>
                <td class="px-5 py-3.5 text-right">
                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-sm text-violet-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No tickets found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($tickets->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
