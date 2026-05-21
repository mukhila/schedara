@extends('layouts.backend')
@section('title', 'Create Invoice — '.$client->client_name)

@section('content')

<div class="max-w-2xl mx-auto">
  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('agency.billing.index', $client->uuid) }}" class="text-ink/40 hover:text-ink">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div>
      <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-1">Create Invoice</div>
      <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ $client->client_name }}</h1>
    </div>
  </div>

  <div class="card p-6">
    <form id="invoice-form" class="space-y-5">
      @csrf
      <div class="grid sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Subscription Plan *</label>
          <select name="subscription_plan" required
                  class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400">
            <option value="">Select plan</option>
            @foreach(['Starter ($99/mo)','Growth ($249/mo)','Agency ($499/mo)','Enterprise (Custom)'] as $plan)
              <option value="{{ $plan }}">{{ $plan }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Amount (in cents) *</label>
          <input type="number" name="amount" required min="0"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="9900">
          <div class="text-xs text-ink/40 mt-1">Enter in cents. E.g. $99.00 = 9900</div>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Tax (in cents)</label>
          <input type="number" name="tax" min="0" value="0"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Currency</label>
          <select name="currency" class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400">
            @foreach(['USD','EUR','GBP','INR','CAD','AUD'] as $c)
              <option value="{{ $c }}">{{ $c }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Provider</label>
          <select name="provider" class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400">
            <option value="stripe">Stripe</option>
            <option value="razorpay">Razorpay</option>
            <option value="manual">Manual</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Due Date</label>
          <input type="date" name="due_date"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 value="{{ now()->addDays(30)->format('Y-m-d') }}">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Notes</label>
          <textarea name="notes" rows="3"
                    class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400 resize-none"
                    placeholder="Invoice notes…"></textarea>
        </div>
      </div>

      <div id="form-error" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-3"></div>

      <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('agency.billing.index', $client->uuid) }}" class="px-5 py-2.5 text-sm font-semibold text-ink/50 hover:text-ink">Cancel</a>
        <button type="submit" id="submit-btn" class="btn-primary">Create Invoice</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('invoice-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  const errEl = document.getElementById('form-error');
  errEl.classList.add('hidden');
  btn.disabled = true;
  btn.textContent = 'Creating…';

  const data = Object.fromEntries(new FormData(e.target));
  data.amount = parseInt(data.amount);
  data.tax    = parseInt(data.tax || 0);

  try {
    const res = await fetch('/api/clients/{{ $client->uuid }}/billing', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify(data),
    });
    const json = await res.json();
    if (!res.ok) {
      const msgs = json.errors ? Object.values(json.errors).flat().join(' ') : (json.message || 'Error');
      errEl.textContent = msgs;
      errEl.classList.remove('hidden');
      btn.disabled = false;
      btn.textContent = 'Create Invoice';
      return;
    }
    window.location.href = '{{ route("agency.billing.index", $client->uuid) }}';
  } catch {
    errEl.textContent = 'Network error.';
    errEl.classList.remove('hidden');
    btn.disabled = false;
    btn.textContent = 'Create Invoice';
  }
});
</script>
@endsection
