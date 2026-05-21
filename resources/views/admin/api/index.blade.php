@extends('admin.layouts.admin')

@section('title', 'API Integrations')
@section('heading', 'API Integrations')

@section('content')
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-500">Monthly API cost: <strong class="text-gray-900">${{ number_format($totalCost / 100, 2) }}</strong></p>
    <button onclick="document.getElementById('add-api-modal').showModal()"
            class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
        + Add Integration
    </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse($integrations as $api)
    <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ editOpen: false }">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="font-semibold text-gray-900">{{ $api->display_name }}</h3>
                <p class="text-xs text-gray-400">{{ $api->provider_name }} · {{ ucfirst($api->environment) }}</p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $api->isActive() ? 'bg-emerald-100 text-emerald-700' : ($api->hasError() ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                {{ ucfirst($api->status) }}
            </span>
        </div>

        <div class="text-xs font-mono bg-gray-50 rounded px-3 py-2 text-gray-600 mb-3">{{ $api->maskedKey() }}</div>

        @if($api->usage_limit)
        <div class="mb-3">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Usage</span>
                <span>{{ number_format($api->current_usage) }} / {{ number_format($api->usage_limit) }} ({{ $api->usagePercent() }}%)</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $api->usagePercent() >= 90 ? 'bg-red-500' : ($api->usagePercent() >= 70 ? 'bg-amber-400' : 'bg-violet-500') }}"
                     style="width: {{ $api->usagePercent() }}%"></div>
            </div>
        </div>
        @endif

        @if($api->last_error)
        <p class="text-xs text-red-600 mb-3 truncate">Error: {{ $api->last_error }}</p>
        @endif

        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.api.health', $api) }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full border border-gray-300 text-gray-700 py-1.5 px-3 rounded-lg text-xs hover:bg-gray-50">
                    Health Check
                </button>
            </form>
            <button @click="editOpen = true" class="border border-violet-300 text-violet-600 py-1.5 px-3 rounded-lg text-xs hover:bg-violet-50">
                Edit
            </button>
            <form method="POST" action="{{ route('admin.api.destroy', $api) }}" onsubmit="return confirm('Remove integration?')">
                @csrf @method('DELETE')
                <button type="submit" class="border border-red-300 text-red-600 py-1.5 px-3 rounded-lg text-xs hover:bg-red-50">
                    Remove
                </button>
            </form>
        </div>

        {{-- Edit Modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div @click.away="editOpen = false" class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
                <h4 class="font-semibold text-gray-900 mb-4">Edit {{ $api->display_name }}</h4>
                <form method="POST" action="{{ route('admin.api.update', $api) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Display Name</label>
                        <input type="text" name="display_name" value="{{ $api->display_name }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">API Key (leave blank to keep)</label>
                        <input type="password" name="api_key" placeholder="••••••••"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                @foreach(['active','inactive','error'] as $s)
                                    <option value="{{ $s }}" {{ $api->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Monthly Cost (cents)</label>
                            <input type="number" name="monthly_cost_cents" value="{{ $api->monthly_cost_cents }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Environment</label>
                        <select name="environment" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            @foreach(['production','sandbox','test'] as $env)
                                <option value="{{ $env }}" {{ $api->environment === $env ? 'selected' : '' }}>{{ ucfirst($env) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 bg-violet-600 text-white py-2 rounded-lg text-sm font-medium">Save</button>
                        <button type="button" @click="editOpen = false" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
        No API integrations configured yet.
    </div>
    @endforelse
</div>

{{-- Add Modal --}}
<dialog id="add-api-modal" class="rounded-2xl p-0 shadow-2xl w-full max-w-md">
    <form method="POST" action="{{ route('admin.api.store') }}" class="p-6 space-y-3">
        @csrf
        <h4 class="font-semibold text-gray-900 mb-1">Add API Integration</h4>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Provider Name (unique key)</label>
            <input type="text" name="provider_name" placeholder="e.g. openai" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Display Name</label>
            <input type="text" name="display_name" placeholder="e.g. OpenAI API" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">API Key</label>
            <input type="password" name="api_key"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Environment</label>
                <select name="environment" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="production">Production</option>
                    <option value="sandbox">Sandbox</option>
                    <option value="test">Test</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Monthly Cost (cents)</label>
                <input type="number" name="monthly_cost_cents" value="0" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-violet-600 text-white py-2 rounded-lg text-sm font-medium">Add</button>
            <button type="button" onclick="document.getElementById('add-api-modal').close()"
                    class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">Cancel</button>
        </div>
    </form>
</dialog>
@endsection
