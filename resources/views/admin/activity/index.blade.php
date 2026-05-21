@extends('admin.layouts.admin')

@section('title', 'Activity Log')
@section('heading', 'Admin Activity Log')

@section('content')
{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <select name="module" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Modules</option>
                @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="admin" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Admins</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}" {{ request('admin') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">Filter</button>
        @if(request()->hasAny(['module','admin','from','to']))
            <a href="{{ route('admin.activity.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2">Clear</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Admin</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Action</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Module</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Description</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">IP</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">When</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($logs as $log)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-gray-900 font-medium">{{ $log->admin?->name ?? 'System' }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-700">
                        {{ $log->action }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600 text-xs">{{ $log->module }}</td>
                <td class="px-4 py-3 text-gray-700 max-w-sm truncate">{{ $log->description }}</td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $log->ip_address }}</td>
                <td class="px-5 py-3 text-right text-gray-400 text-xs" title="{{ $log->created_at }}">
                    {{ $log->created_at->diffForHumans() }}
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No activity found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($logs->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
