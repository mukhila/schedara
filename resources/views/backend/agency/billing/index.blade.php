@extends('layouts.backend')
@section('title', 'Billing — '.$client->client_name)

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('agency.clients.show', $client->uuid) }}" class="text-ink/40 hover:text-ink">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div>
      <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-1">Billing</div>
      <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ $client->client_name }}</h1>
    </div>
  </div>
  <a href="{{ route('agency.billing.create', $client->uuid) }}" class="btn-primary flex items-center gap-1.5">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    New Invoice
  </a>
</div>

{{-- Revenue stats --}}
<div class="grid sm:grid-cols-3 gap-4 mb-6">
  @foreach([
    ['Total Revenue',  '$'.number_format(($revenue['total_revenue']??0)/100,2), 'pill-mint'],
    ['This Month',     '$'.number_format(($revenue['this_month']??0)/100,2),   'pill-brand'],
    ['Paying Clients', $revenue['paying_clients']??0,                           'pill-gold'],
  ] as [$label,$val,$pill])
  <div class="card p-5">
    <div class="text-xs font-bold uppercase tracking-wider text-ink/40 mb-2">{{ $label }}</div>
    <div class="text-2xl font-extrabold">{{ $val }}</div>
    <span class="pill {{ $pill }} mt-2">{{ $val }}</span>
  </div>
  @endforeach
</div>

{{-- Invoices table --}}
<div class="card">
  @if($invoices->count())
  <table class="w-full text-sm">
    <thead>
      <tr class="text-xs text-ink/40 border-b border-line">
        <th class="text-left px-5 py-3 font-semibold">Invoice #</th>
        <th class="text-left px-5 py-3 font-semibold">Plan</th>
        <th class="text-left px-5 py-3 font-semibold">Due</th>
        <th class="text-right px-5 py-3 font-semibold">Total</th>
        <th class="text-right px-5 py-3 font-semibold">Status</th>
        <th class="text-right px-5 py-3 font-semibold">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-line">
      @foreach($invoices as $inv)
      <tr class="hover:bg-paper/60">
        <td class="px-5 py-3 font-mono text-xs">{{ $inv->invoice_number }}</td>
        <td class="px-5 py-3">{{ $inv->subscription_plan }}</td>
        <td class="px-5 py-3 text-ink/50">{{ $inv->due_date?->format('M d, Y') ?? '—' }}</td>
        <td class="px-5 py-3 text-right font-bold">{{ $inv->formattedTotal() }}</td>
        <td class="px-5 py-3 text-right">
          <span class="pill {{ $inv->isPaid()?'pill-mint':($inv->isOverdue()?'pill-coral':'pill-gold') }}">
            {{ ucfirst($inv->payment_status) }}
          </span>
        </td>
        <td class="px-5 py-3 text-right">
          @if(!$inv->isPaid())
          <button onclick="markPaid('{{ $inv->uuid }}')"
                  class="text-xs font-semibold text-brand-600 hover:underline">
            Mark Paid
          </button>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="px-5 py-3 border-t border-line">
    {{ $invoices->links() }}
  </div>
  @else
  <div class="p-12 text-center">
    <div class="text-4xl mb-3">🧾</div>
    <h3 class="font-bold mb-1">No invoices yet</h3>
    <p class="text-sm text-ink/50 mb-4">Create the first invoice for this client.</p>
    <a href="{{ route('agency.billing.create', $client->uuid) }}" class="btn-primary">New Invoice</a>
  </div>
  @endif
</div>

<script>
async function markPaid(uuid) {
  if (!confirm('Mark this invoice as paid?')) return;
  const res = await fetch(`/api/clients/billing/${uuid}/mark-paid`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
  });
  if (res.ok) location.reload();
}
</script>
@endsection
