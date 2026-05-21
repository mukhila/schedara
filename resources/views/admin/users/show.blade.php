@extends('admin.layouts.admin')

@section('title', $user->name)
@section('heading', 'User: ' . $user->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-bold text-xl">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="font-semibold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                @if($user->is_super_admin)
                    <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">Super Admin</span>
                @endif
            </div>
        </div>

        <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-500">Status</dt>
                <dd>
                    @if($user->suspended_at)
                        <span class="text-red-600 font-medium">Suspended</span>
                    @else
                        <span class="text-emerald-600 font-medium">Active</span>
                    @endif
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Joined</dt>
                <dd class="text-gray-700">{{ $user->created_at->format('M d, Y') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Workspaces</dt>
                <dd class="text-gray-700">{{ $user->tenants->count() }}</dd>
            </div>
        </dl>

        <div class="pt-4 border-t border-gray-100 space-y-2">
            @if($user->suspended_at)
            <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                @csrf
                <button class="w-full bg-emerald-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-emerald-700">Activate User</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                @csrf
                <input type="hidden" name="reason" value="Manual admin action">
                <button class="w-full bg-amber-500 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-amber-600">Suspend User</button>
            </form>
            @endif

            @if(!$user->is_super_admin)
            <form method="POST" action="{{ route('admin.users.make-admin', $user) }}">
                @csrf
                <button class="w-full border border-violet-600 text-violet-600 py-2 px-4 rounded-lg text-sm font-medium hover:bg-violet-50">Grant Admin</button>
            </form>
            <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                @csrf
                <button class="w-full border border-gray-300 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-50">Impersonate</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.users.revoke-admin', $user) }}">
                @csrf
                <button class="w-full border border-red-300 text-red-600 py-2 px-4 rounded-lg text-sm font-medium hover:bg-red-50">Revoke Admin</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Workspaces --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Workspaces</h3>
        @forelse($user->tenants as $tenant)
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
                <p class="font-medium text-gray-900">{{ $tenant->name }}</p>
                <p class="text-xs text-gray-400">{{ $tenant->domain ?? $tenant->slug }}</p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst($tenant->status) }}
            </span>
        </div>
        @empty
        <p class="text-sm text-gray-400 py-4 text-center">No workspaces.</p>
        @endforelse
    </div>
</div>
@endsection
