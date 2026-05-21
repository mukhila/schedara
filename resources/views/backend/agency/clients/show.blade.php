@extends('layouts.backend')
@section('title', $client->client_name)

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('agency.dashboard') }}" class="text-ink/40 hover:text-ink">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    @if($client->logo)
      <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->client_name }}" class="w-12 h-12 rounded-xl object-cover">
    @else
      <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl font-bold text-white"
           style="background:linear-gradient(135deg,#65a1d8,#235b95)">
        {{ strtoupper(mb_substr($client->client_name,0,1)) }}
      </div>
    @endif
    <div>
      <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ $client->client_name }}</h1>
      <div class="text-sm text-ink/50">{{ $client->company_name }} · {{ $client->email }}</div>
    </div>
  </div>
  <div class="flex gap-2">
    @php
      $pillClass = match($client->status) { 'active'=>'pill-mint','onboarding'=>'pill-gold','suspended'=>'pill-coral',default=>'pill-brand' };
    @endphp
    <span class="pill {{ $pillClass }} pill-dot">{{ ucfirst($client->status) }}</span>
    <a href="{{ route('agency.clients.onboarding', $client->uuid) }}"
       class="btn-primary flex items-center gap-1.5 text-sm">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      Onboarding
    </a>
  </div>
</div>

{{-- Info grid --}}
<div class="grid lg:grid-cols-3 gap-4 mb-6">
  <div class="card p-5 col-span-2">
    <h3 class="font-bold mb-4">Client Details</h3>
    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
      @foreach([
        ['Phone',    $client->phone    ?: '—'],
        ['Website',  $client->website  ?: '—'],
        ['Industry', $client->industry ?: '—'],
        ['Timezone', $client->timezone],
        ['Created',  $client->created_at->format('M d, Y')],
      ] as [$label,$val])
      <div>
        <dt class="text-xs text-ink/40 font-semibold uppercase tracking-wider mb-0.5">{{ $label }}</dt>
        <dd class="font-medium">{{ $val }}</dd>
      </div>
      @endforeach
    </dl>
  </div>

  <div class="card p-5">
    <h3 class="font-bold mb-3">Onboarding Progress</h3>
    <div class="flex items-center gap-3 mb-3">
      <div class="text-3xl font-extrabold text-brand-600">{{ $progress }}%</div>
      <div class="flex-1 h-2 bg-line rounded-full overflow-hidden">
        <div class="h-full rounded-full" style="width:{{ $progress }}%;background:var(--brand)"></div>
      </div>
    </div>
    <ul class="space-y-1.5">
      @foreach($client->onboardingSteps as $step)
      <li class="flex items-center gap-2 text-sm">
        @if($step->isCompleted())
          <svg class="w-4 h-4 text-mint flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        @else
          <div class="w-4 h-4 rounded-full border-2 border-line flex-shrink-0"></div>
        @endif
        <span class="{{ $step->isCompleted() ? 'text-ink/40 line-through' : 'font-medium' }}">
          {{ ucfirst(str_replace('_',' ',$step->onboarding_step)) }}
        </span>
        <span class="ml-auto pill {{ $step->status === 'completed' ? 'pill-mint' : ($step->status === 'in_progress' ? 'pill-gold' : 'pill-brand') }} text-[10px]">
          {{ ucfirst(str_replace('_',' ',$step->status)) }}
        </span>
      </li>
      @endforeach
    </ul>
    <a href="{{ route('agency.clients.onboarding', $client->uuid) }}"
       class="mt-4 block text-center text-sm font-semibold text-brand-600 hover:underline">
      Continue Onboarding →
    </a>
  </div>
</div>

{{-- Quick Actions --}}
<div class="grid sm:grid-cols-3 gap-4 mb-6">
  @if($client->workspace)
  <a href="{{ route('agency.billing.index', $client->uuid) }}"
     class="card p-5 flex items-center gap-3 hover:shadow-md transition group">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(101,161,216,.12)">
      <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 8h20M6 12h2M10 12h4"/></svg>
    </div>
    <div>
      <div class="font-bold text-sm group-hover:text-brand-600">Billing</div>
      <div class="text-xs text-ink/40">{{ $client->billing->count() }} invoice(s)</div>
    </div>
    <svg class="w-4 h-4 text-ink/20 ml-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
  </a>
  <a href="{{ route('agency.white-label.edit', $client->workspace->uuid) }}"
     class="card p-5 flex items-center gap-3 hover:shadow-md transition group">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(34,176,126,.1)">
      <svg class="w-5 h-5 text-mint" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8"/></svg>
    </div>
    <div>
      <div class="font-bold text-sm group-hover:text-mint">White-label</div>
      <div class="text-xs text-ink/40">Branding settings</div>
    </div>
    <svg class="w-4 h-4 text-ink/20 ml-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
  </a>
  @endif
  <div class="card p-5 flex items-center gap-3"
       x-data="{showDelete:false}">
    <button @click="showDelete=true"
            class="flex items-center gap-3 w-full text-left hover:opacity-80 transition">
      <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(255,64,28,.1)">
        <svg class="w-5 h-5 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
      </div>
      <div>
        <div class="font-bold text-sm text-coral">Delete Client</div>
        <div class="text-xs text-ink/40">Permanent action</div>
      </div>
    </button>
    <div x-show="showDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50" style="display:none">
      <div class="card p-6 max-w-sm w-full mx-4">
        <h3 class="font-bold text-lg mb-2">Delete client?</h3>
        <p class="text-sm text-ink/60 mb-5">This will permanently delete <strong>{{ $client->client_name }}</strong> and all associated data.</p>
        <div class="flex gap-3">
          <button @click="showDelete=false" class="flex-1 px-4 py-2 text-sm border border-line rounded-lg">Cancel</button>
          <button onclick="deleteClient('{{ $client->uuid }}')"
                  class="flex-1 px-4 py-2 text-sm bg-coral text-white rounded-lg font-semibold">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Recent Invoices --}}
@if($client->billing->count())
<div class="card p-5">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold">Recent Invoices</h3>
    <a href="{{ route('agency.billing.index', $client->uuid) }}" class="text-xs text-brand-600 font-semibold hover:underline">View All</a>
  </div>
  <table class="w-full text-sm">
    <thead><tr class="text-xs text-ink/40 border-b border-line">
      <th class="text-left py-2 pb-3 font-semibold">Invoice</th>
      <th class="text-left py-2 pb-3 font-semibold">Plan</th>
      <th class="text-right py-2 pb-3 font-semibold">Total</th>
      <th class="text-right py-2 pb-3 font-semibold">Status</th>
    </tr></thead>
    <tbody class="divide-y divide-line">
      @foreach($client->billing->take(5) as $inv)
      <tr>
        <td class="py-2.5 font-mono text-xs">{{ $inv->invoice_number }}</td>
        <td class="py-2.5">{{ $inv->subscription_plan }}</td>
        <td class="py-2.5 text-right font-semibold">{{ $inv->formattedTotal() }}</td>
        <td class="py-2.5 text-right">
          <span class="pill {{ $inv->isPaid()?'pill-mint':'pill-gold' }}">{{ ucfirst($inv->payment_status) }}</span>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

<script>
async function deleteClient(uuid) {
  try {
    const res = await fetch(`/api/clients/${uuid}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
    });
    if (res.ok) window.location.href = '{{ route("agency.dashboard") }}';
  } catch(e) { alert('Error deleting client.'); }
}
</script>
@endsection
