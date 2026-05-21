@extends('layouts.backend')
@section('title', 'Agency Dashboard')

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
  <div>
    <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Agency</div>
    <h1 class="text-3xl font-extrabold tracking-tight text-ink">Client Management</h1>
  </div>
  <a href="{{ route('agency.clients.create') }}" class="btn-primary flex items-center gap-1.5">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    Add Client
  </a>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  @foreach([
    ['label'=>'Total Clients',    'value'=>number_format($stats['total_clients']),    'pill'=>'pill-brand'],
    ['label'=>'Active Clients',   'value'=>number_format($stats['active_clients']),   'pill'=>'pill-mint'],
    ['label'=>'Onboarding',       'value'=>number_format($stats['onboarding_clients']),'pill'=>'pill-gold'],
    ['label'=>'Monthly Revenue',  'value'=>'$'.number_format(($stats['this_month']??0)/100,2), 'pill'=>'pill-mint'],
  ] as $kpi)
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">{{ $kpi['label'] }}</div>
    <div class="text-3xl font-extrabold tracking-tight">{{ $kpi['value'] }}</div>
    <div class="mt-1"><span class="pill {{ $kpi['pill'] }}">{{ $kpi['value'] }}</span></div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<div class="card p-4 mb-4 flex flex-wrap gap-3 items-center">
  <form method="GET" class="flex flex-wrap gap-2 flex-1">
    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
           placeholder="Search clients…"
           class="border border-line rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-brand-400 flex-1 min-w-[160px]">
    <select name="status" class="border border-line rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-brand-400">
      <option value="">All Status</option>
      @foreach(['active','onboarding','inactive','suspended','churned'] as $s)
        <option value="{{ $s }}" {{ ($filters['status']??'')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
    <button type="submit" class="btn-primary text-sm py-1.5">Filter</button>
    @if(!empty(array_filter($filters)))
      <a href="{{ route('agency.dashboard') }}" class="text-sm text-ink/50 px-3 py-1.5 hover:text-ink">Clear</a>
    @endif
  </form>
</div>

{{-- Client grid --}}
@if($clients->count())
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
  @foreach($clients as $client)
  <a href="{{ route('agency.clients.show', $client->uuid) }}"
     class="card p-5 flex flex-col gap-3 hover:shadow-md transition-shadow group">
    <div class="flex items-start gap-3">
      @if($client->logo)
        <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->client_name }}"
             class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
      @else
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg font-bold text-white flex-shrink-0"
             style="background:linear-gradient(135deg,#65a1d8,#235b95)">
          {{ strtoupper(mb_substr($client->client_name,0,1)) }}
        </div>
      @endif
      <div class="min-w-0 flex-1">
        <div class="font-bold text-ink truncate group-hover:text-brand-600">{{ $client->client_name }}</div>
        <div class="text-xs text-ink/50 truncate">{{ $client->company_name ?: $client->email }}</div>
      </div>
      @php
        $pillClass = match($client->status) {
          'active'     => 'pill-mint',
          'onboarding' => 'pill-gold',
          'suspended'  => 'pill-coral',
          default      => 'pill-brand',
        };
      @endphp
      <span class="pill {{ $pillClass }} pill-dot flex-shrink-0">{{ ucfirst($client->status) }}</span>
    </div>
    <div class="grid grid-cols-2 gap-2 text-xs">
      <div class="bg-paper rounded-lg p-2">
        <div class="text-ink/40 mb-0.5">Industry</div>
        <div class="font-semibold truncate">{{ $client->industry ?: '—' }}</div>
      </div>
      <div class="bg-paper rounded-lg p-2">
        <div class="text-ink/40 mb-0.5">Onboarding</div>
        <div class="font-semibold">{{ $client->onboardingProgress() }}%</div>
      </div>
    </div>
    <div class="h-1.5 bg-line rounded-full overflow-hidden">
      <div class="h-full rounded-full transition-all" style="width:{{ $client->onboardingProgress() }}%;background:var(--brand)"></div>
    </div>
  </a>
  @endforeach
</div>

{{-- Pagination --}}
<div class="flex justify-center">
  {{ $clients->withQueryString()->links() }}
</div>
@else
<div class="card p-16 text-center">
  <div class="text-4xl mb-3">🏢</div>
  <h3 class="text-lg font-bold mb-1">No clients yet</h3>
  <p class="text-sm text-ink/50 mb-4">Start by adding your first client.</p>
  <a href="{{ route('agency.clients.create') }}" class="btn-primary inline-flex items-center gap-1.5">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    Add First Client
  </a>
</div>
@endif

@endsection
