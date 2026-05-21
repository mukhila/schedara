@extends('layouts.backend')
@section('title', 'Add New Client')

@section('content')

<div class="max-w-2xl mx-auto">
  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('agency.dashboard') }}" class="text-ink/40 hover:text-ink">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div>
      <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-1">Agency</div>
      <h1 class="text-2xl font-extrabold tracking-tight text-ink">Add New Client</h1>
    </div>
  </div>

  <div class="card p-6">
    <form id="create-client-form" class="space-y-5">
      @csrf
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Client Name *</label>
          <input type="text" name="client_name" required
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="Jane Smith">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Email *</label>
          <input type="email" name="email" required
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="jane@company.com">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Company Name</label>
          <input type="text" name="company_name"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="Acme Corp">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Phone</label>
          <input type="text" name="phone"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="+1 555 000 0000">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Website</label>
          <input type="url" name="website"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="https://company.com">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Industry</label>
          <select name="industry" class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400">
            <option value="">Select industry</option>
            @foreach(['E-Commerce','Healthcare','Finance','Technology','Real Estate','Education','Fashion','Food & Beverage','Travel','Entertainment','Non-Profit','Other'] as $ind)
              <option value="{{ $ind }}">{{ $ind }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Timezone</label>
          <select name="timezone" class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400">
            @foreach(timezone_identifiers_list() as $tz)
              <option value="{{ $tz }}" {{ $tz === 'UTC' ? 'selected' : '' }}>{{ $tz }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Workspace Name</label>
          <input type="text" name="workspace_name"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="Acme Corp Workspace">
        </div>
      </div>

      <div id="form-error" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-3"></div>

      <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('agency.dashboard') }}" class="px-5 py-2.5 text-sm font-semibold text-ink/50 hover:text-ink">Cancel</a>
        <button type="submit" id="submit-btn" class="btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
          Create Client
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('create-client-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  const errEl = document.getElementById('form-error');
  errEl.classList.add('hidden');
  btn.disabled = true;
  btn.textContent = 'Creating…';

  const data = Object.fromEntries(new FormData(e.target));

  try {
    const res = await fetch('/api/clients', {
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
      btn.innerHTML = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Create Client';
      return;
    }
    window.location.href = `/agency/clients/${json.client.uuid}`;
  } catch (err) {
    errEl.textContent = 'Network error. Please try again.';
    errEl.classList.remove('hidden');
    btn.disabled = false;
    btn.innerHTML = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Create Client';
  }
});
</script>
@endsection
