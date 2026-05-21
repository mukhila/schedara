@extends('admin.layouts.admin')

@section('title', 'Plans')
@section('heading', 'Plan Management')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.plans.create') }}" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
        + New Plan
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Plan</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Monthly</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Yearly</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Subscribers</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Visibility</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($plans as $plan)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5">
                    <p class="font-medium text-gray-900">{{ $plan->name }}</p>
                    <p class="text-xs text-gray-400">{{ $plan->slug }}</p>
                </td>
                <td class="px-4 py-3.5 text-gray-700">${{ number_format($plan->price_monthly / 100, 2) }}</td>
                <td class="px-4 py-3.5 text-gray-700">
                    {{ $plan->price_yearly ? '$' . number_format($plan->price_yearly / 100, 2) : '—' }}
                </td>
                <td class="px-4 py-3.5 text-gray-700">{{ $plan->subscriptions_count }}</td>
                <td class="px-4 py-3.5">
                    @if($plan->is_public ?? true)
                        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Public</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Hidden</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right space-x-2">
                    <a href="{{ route('admin.plans.edit', $plan) }}" class="text-sm text-violet-600 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="inline"
                          onsubmit="return confirm('Delete this plan?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No plans found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
