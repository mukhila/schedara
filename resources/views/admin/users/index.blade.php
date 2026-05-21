@extends('admin.layouts.admin')

@section('title', 'Users')
@section('heading', 'User Management')

@section('content')
{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name or email…"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
        </div>
        <div>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                <option value="">All Status</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="admin"     {{ request('status') === 'admin'     ? 'selected' : '' }}>Admins</option>
            </select>
        </div>
        <button type="submit" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">User</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Workspaces</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Joined</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-semibold text-xs">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $user->name }}
                                @if($user->is_super_admin)
                                    <span class="ml-1 text-xs bg-violet-100 text-violet-700 px-1.5 py-0.5 rounded">Admin</span>
                                @endif
                            </p>
                            <p class="text-gray-400 text-xs">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3.5 text-gray-600">{{ $user->tenants_count }}</td>
                <td class="px-4 py-3.5">
                    @if($user->suspended_at)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Suspended</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Active</span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-gray-500 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3.5 text-right" x-data="{ open: false }">
                    <div class="relative inline-block text-left">
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-1 rounded">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-10 py-1">
                            <a href="{{ route('admin.users.show', $user) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Profile</a>
                            @if($user->suspended_at)
                            <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-emerald-600 hover:bg-gray-50">Activate</button>
                            </form>
                            @else
                            <button onclick="document.getElementById('suspend-{{ $user->id }}').showModal()"
                                    class="block w-full text-left px-4 py-2 text-sm text-amber-600 hover:bg-gray-50">Suspend</button>
                            @endif
                            @if(!$user->is_super_admin)
                            <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50">Impersonate</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    {{-- Suspend Dialog --}}
                    <dialog id="suspend-{{ $user->id }}" class="rounded-xl p-0 shadow-xl w-80">
                        <form method="POST" action="{{ route('admin.users.suspend', $user) }}" class="p-5">
                            @csrf
                            <h4 class="font-semibold text-gray-900 mb-3">Suspend {{ $user->name }}?</h4>
                            <textarea name="reason" rows="3" placeholder="Reason (optional)"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 mb-3"></textarea>
                            <div class="flex gap-2 justify-end">
                                <button type="button" onclick="this.closest('dialog').close()"
                                        class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                                <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Suspend</button>
                            </div>
                        </form>
                    </dialog>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
