@extends('admin.layouts.admin')

@section('title', 'Subscription')
@section('heading', 'Subscription Details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 mb-0.5">Workspace</dt>
                <dd class="font-medium text-gray-900">{{ $subscription->tenant?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-0.5">Plan</dt>
                <dd class="font-medium text-gray-900">{{ $subscription->plan?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-0.5">Status</dt>
                <dd>
                    @php $colors = ['active'=>'emerald','trialing'=>'blue','cancelled'=>'red','paused'=>'amber','past_due'=>'orange']; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $colors[$subscription->status] ?? 'gray' }}-100 text-{{ $colors[$subscription->status] ?? 'gray' }}-700 font-medium">
                        {{ ucfirst($subscription->status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-0.5">Interval</dt>
                <dd class="text-gray-700 capitalize">{{ $subscription->interval }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-0.5">Provider</dt>
                <dd class="text-gray-700 capitalize">{{ $subscription->provider }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-0.5">Provider ID</dt>
                <dd class="text-gray-500 text-xs font-mono">{{ $subscription->provider_id }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-0.5">Started</dt>
                <dd class="text-gray-700">{{ $subscription->created_at->format('M d, Y') }}</dd>
            </div>
            @if($subscription->trial_ends_at)
            <div>
                <dt class="text-gray-500 mb-0.5">Trial Ends</dt>
                <dd class="text-gray-700">{{ $subscription->trial_ends_at->format('M d, Y') }}</dd>
            </div>
            @endif
            @if($subscription->cancel_at)
            <div>
                <dt class="text-gray-500 mb-0.5">Cancels</dt>
                <dd class="text-red-600">{{ $subscription->cancel_at->format('M d, Y') }}</dd>
            </div>
            @endif
        </dl>

        <div class="pt-4 border-t border-gray-100 flex gap-3">
            @if(in_array($subscription->status, ['active','trialing']))
            <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription) }}"
                  onsubmit="return confirm('Cancel this subscription?')">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">
                    Cancel Subscription
                </button>
            </form>
            @endif

            @if(in_array($subscription->status, ['active','trialing']))
            <form method="POST" action="{{ route('admin.subscriptions.extend-trial', $subscription) }}" class="flex gap-2">
                @csrf
                <input type="number" name="days" value="7" min="1" max="90"
                       class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <button type="submit" class="border border-violet-600 text-violet-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-50">
                    Extend Trial (days)
                </button>
            </form>
            @endif

            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">← Back</a>
        </div>
    </div>
</div>
@endsection
