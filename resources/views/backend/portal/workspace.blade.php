@extends('layouts.backend')
@section('title', $workspace->workspace_name.' — Portal')

@section('styles')
@if($settings)
<style>:root { {!! $settings->cssVariables() !!} }</style>
@endif
@endsection

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('portal.dashboard') }}" class="text-ink/40 hover:text-ink">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div>
      @if($settings?->brand_name)
        <div class="text-xs font-bold uppercase tracking-[2px] mb-1" style="color:var(--primary)">{{ $settings->brand_name }}</div>
      @endif
      <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ $workspace->workspace_name }}</h1>
    </div>
  </div>
  <span class="pill pill-brand">{{ ucfirst($role) }}</span>
</div>

{{-- Quick info --}}
<div class="grid sm:grid-cols-3 gap-4 mb-6">
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/40 mb-2">Client</div>
    <div class="font-bold">{{ $client->client_name }}</div>
    <div class="text-xs text-ink/50">{{ $client->company_name }}</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/40 mb-2">Industry</div>
    <div class="font-bold">{{ $client->industry ?: '—' }}</div>
  </div>
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/40 mb-2">Status</div>
    @php $pillClass = match($client->status){'active'=>'pill-mint','onboarding'=>'pill-gold',default=>'pill-brand'}; @endphp
    <span class="pill {{ $pillClass }} pill-dot">{{ ucfirst($client->status) }}</span>
  </div>
</div>

{{-- Recent Reports --}}
<div class="card p-5">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold">Recent Reports</h3>
  </div>
  @if($reports->count())
  <div class="space-y-3">
    @foreach($reports as $report)
    <div class="flex items-center gap-3 p-3 bg-paper rounded-xl">
      <div class="w-9 h-9 rounded-lg flex items-center justify-center text-brand-600" style="background:rgba(101,161,216,.12)">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div class="flex-1 min-w-0">
        <div class="font-semibold text-sm truncate">{{ $report->report_name }}</div>
        <div class="text-xs text-ink/40">{{ ucfirst(str_replace('_',' ',$report->report_type)) }} · {{ $report->generated_at?->format('M d, Y') ?? 'Pending' }}</div>
      </div>
      <span class="pill {{ $report->isReady() ? 'pill-mint' : 'pill-gold' }}">{{ ucfirst($report->status) }}</span>
      @if($report->isReady())
      <a href="/api/workspaces/{{ $workspace->uuid }}/reports/{{ $report->uuid }}/download"
         class="text-xs font-semibold text-brand-600 hover:underline flex-shrink-0">
        Download
      </a>
      @endif
    </div>
    @endforeach
  </div>
  @else
  <div class="text-center py-8 text-sm text-ink/40">No reports available yet.</div>
  @endif
</div>

@endsection
