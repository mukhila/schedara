@extends('layouts.backend')
@section('title', 'Campaign Analytics')

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-4">
  <div>
    <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Analytics</div>
    <h1 class="text-3xl font-extrabold tracking-tight text-ink">Campaigns</h1>
  </div>
  <button onclick="document.getElementById('newCampaignModal').classList.remove('hidden')"
    class="bg-brand-600 text-white font-semibold text-sm px-4 py-2 rounded-lg hover:bg-brand-700 transition-colors flex items-center gap-1.5">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    New Campaign
  </button>
</div>

@include('backend.analytics._nav')
@include('backend.analytics._filter_bar')

@php $sum = $summary ?? []; @endphp

{{-- Summary KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
  @foreach([
    ['label' => 'Campaigns',   'value' => number_format($sum['total_campaigns'] ?? 0)],
    ['label' => 'Total Spend', 'value' => '$' . number_format($sum['total_spend'] ?? 0, 0)],
    ['label' => 'Revenue',     'value' => '$' . number_format($sum['total_revenue'] ?? 0, 0)],
    ['label' => 'Avg ROI',     'value' => number_format($sum['avg_roi'] ?? 0, 1) . '%'],
    ['label' => 'Avg ROAS',    'value' => number_format($sum['avg_roas'] ?? 0, 2) . 'x'],
  ] as $c)
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/50">{{ $c['label'] }}</div>
    <div class="mt-3 text-2xl font-extrabold tracking-tight">{{ $c['value'] }}</div>
  </div>
  @endforeach
</div>

{{-- Campaigns table --}}
<div class="card p-6">
  <h3 class="text-lg font-bold mb-4">Campaign performance</h3>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-[10px] font-bold uppercase tracking-wider text-ink/40 text-left border-b border-line">
          <th class="py-2 font-bold">Campaign</th>
          <th class="py-2 font-bold">Platform</th>
          <th class="py-2 font-bold">Status</th>
          <th class="py-2 text-right font-bold">Budget</th>
          <th class="py-2 text-right font-bold">Spend</th>
          <th class="py-2 text-right font-bold">Revenue</th>
          <th class="py-2 text-right font-bold">ROI</th>
          <th class="py-2 text-right font-bold">ROAS</th>
          <th class="py-2 text-right font-bold">Conv.</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        @forelse($campaigns as $c)
        @php
          $statusPill = match($c->status) {
            'active'    => 'pill-mint',
            'completed' => 'pill-brand',
            'paused'    => 'pill-gold',
            default     => '',
          };
          $roiColor = $c->roi >= 100 ? 'text-mint' : ($c->roi >= 0 ? 'text-ink' : 'text-coral');
        @endphp
        <tr class="hover:bg-paper/80 transition-colors">
          <td class="py-3">
            <div class="font-bold text-ink">{{ $c->name }}</div>
            <div class="text-xs text-ink/50">{{ $c->start_date->format('M d') }} – {{ $c->end_date?->format('M d') ?? 'ongoing' }}</div>
          </td>
          <td class="py-3 capitalize text-ink/70">{{ $c->platform ?? 'All' }}</td>
          <td class="py-3"><span class="pill {{ $statusPill }}">{{ ucfirst($c->status) }}</span></td>
          <td class="py-3 text-right">${{ number_format($c->budget, 0) }}</td>
          <td class="py-3 text-right">${{ number_format($c->spend, 0) }}</td>
          <td class="py-3 text-right font-semibold text-mint">${{ number_format($c->revenue, 0) }}</td>
          <td class="py-3 text-right font-bold {{ $roiColor }}">{{ number_format($c->roi, 1) }}%</td>
          <td class="py-3 text-right">{{ number_format($c->roas, 2) }}×</td>
          <td class="py-3 text-right">{{ number_format($c->conversions) }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="py-12 text-center text-ink/40 text-sm">
            No campaigns for this period.
            <button onclick="document.getElementById('newCampaignModal').classList.remove('hidden')"
              class="text-brand-600 font-semibold hover:underline">Create one</button>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $campaigns->links() }}
</div>

{{-- New campaign modal --}}
<div id="newCampaignModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-ink/40">
  <div class="card p-6 w-full max-w-lg mx-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold">New Campaign</h3>
      <button onclick="document.getElementById('newCampaignModal').classList.add('hidden')" class="text-ink/40 hover:text-ink">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="campaignForm">
      @csrf
      <div class="space-y-3">
        <div>
          <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Campaign name</label>
          <input type="text" name="name" required class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Platform</label>
            <select name="platform" class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
              <option value="">All platforms</option>
              @foreach(['instagram','facebook','twitter','linkedin','tiktok','youtube'] as $p)
              <option value="{{ $p }}">{{ ucfirst($p) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Budget ($)</label>
            <input type="number" name="budget" step="0.01" min="0" class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Start date</label>
            <input type="date" name="start_date" required class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
          </div>
          <div>
            <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">End date</label>
            <input type="date" name="end_date" class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
          </div>
        </div>
      </div>
      <div class="mt-5 flex gap-2 justify-end">
        <button type="button" onclick="document.getElementById('newCampaignModal').classList.add('hidden')"
          class="px-4 py-2 text-sm font-semibold text-ink/60 hover:text-ink border border-line rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-semibold bg-brand-600 text-white rounded-lg hover:bg-brand-700">Create Campaign</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
const TENANT_ID = '{{ app("current.tenant")->id }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('campaignForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  const body = {};
  fd.forEach((v, k) => { if (v) body[k] = v; });

  const res = await fetch('/api/analytics/campaigns', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Tenant-ID': TENANT_ID },
    body: JSON.stringify(body),
  });

  if (res.ok) { location.reload(); }
  else { const err = await res.json(); alert(Object.values(err.errors || {err:'Error'}).flat().join('\n')); }
});
</script>
@endpush
