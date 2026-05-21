@extends('admin.layouts.admin')

@section('title', isset($plan) ? 'Edit Plan' : 'New Plan')
@section('heading', isset($plan) ? 'Edit Plan: ' . $plan->name : 'New Plan')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ isset($plan) ? route('admin.plans.update', $plan) : route('admin.plans.store') }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf
        @if(isset($plan)) @method('PUT') @endif

        @if($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name *</label>
                <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Price (cents) *</label>
                <input type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}" min="0" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                <p class="text-xs text-gray-400 mt-1">e.g. 2900 = $29.00</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Yearly Price (cents)</label>
                <input type="number" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly ?? '') }}" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Trial Days</label>
                <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days ?? 0) }}" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">{{ old('description', $plan->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_public" id="is_public" value="1"
                   {{ old('is_public', $plan->is_public ?? true) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-violet-600">
            <label for="is_public" class="text-sm text-gray-700">Publicly visible on pricing page</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-violet-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
                {{ isset($plan) ? 'Save Changes' : 'Create Plan' }}
            </button>
            <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
        </div>
    </form>
</div>
@endsection
