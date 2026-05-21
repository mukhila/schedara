@extends('layouts.backend')
@section('title', 'Client Portal')

@section('content')

<div class="mb-6">
  <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Client Portal</div>
  <h1 class="text-3xl font-extrabold tracking-tight text-ink">Your Workspaces</h1>
  <p class="text-sm text-ink/50 mt-1">Access your agency dashboards and reports.</p>
</div>

@if($memberships->count())
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  @foreach($memberships as $membership)
  @php $ws = $membership->workspace; $client = $ws?->client; @endphp
  @if($ws && $client)
  <a href="{{ route('portal.workspace', $ws->uuid) }}"
     class="card p-5 flex flex-col gap-3 hover:shadow-md transition group">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl font-bold text-white flex-shrink-0"
           style="background:linear-gradient(135deg,#65a1d8,#235b95)">
        {{ strtoupper(mb_substr($client->client_name,0,1)) }}
      </div>
      <div class="min-w-0">
        <div class="font-bold truncate group-hover:text-brand-600">{{ $ws->workspace_name }}</div>
        <div class="text-xs text-ink/50">{{ $client->client_name }}</div>
      </div>
    </div>
    <div class="flex items-center justify-between">
      <span class="pill pill-brand">{{ ucfirst($membership->role) }}</span>
      <svg class="w-4 h-4 text-ink/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </div>
  </a>
  @endif
  @endforeach
</div>
@else
<div class="card p-16 text-center">
  <div class="text-4xl mb-3">🏢</div>
  <h3 class="text-lg font-bold mb-1">No workspaces</h3>
  <p class="text-sm text-ink/50">You haven't been added to any client workspaces yet.</p>
</div>
@endif

@endsection
