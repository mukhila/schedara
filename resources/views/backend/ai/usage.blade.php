@extends('layouts.backend')
@section('title', 'AI Usage & Billing')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Usage & Billing</h1>
    <p class="text-ink/60 text-sm mb-6">Monitor your AI token consumption and costs.</p>

    {{-- Usage bar --}}
    <div class="card p-6 mb-5">
      <div class="flex items-end justify-between mb-2">
        <div>
          <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Monthly Token Usage</div>
          <div class="flex items-baseline gap-2 mt-1">
            <span id="usedTokens" class="text-3xl font-extrabold text-ink">—</span>
            <span class="text-sm text-ink/50">/ <span id="limitTokens">—</span> tokens</span>
          </div>
        </div>
        <div class="text-right">
          <div class="text-xs text-ink/40">Resets <span id="resetDate">—</span></div>
          <div id="usagePct" class="text-xl font-extrabold text-ink mt-1">—%</div>
        </div>
      </div>
      <div class="h-3 bg-paper rounded-full overflow-hidden">
        <div id="usageBar" class="h-full rounded-full transition-all duration-700 bg-brand-500" style="width:0%"></div>
      </div>
      <div id="limitWarning" class="hidden mt-3 text-sm font-semibold text-amber-600 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        You've used over 80% of your monthly limit.
      </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <div class="card p-4 text-center">
        <div id="statRequests" class="text-2xl font-extrabold text-ink">—</div>
        <div class="text-[10px] font-bold text-ink/40 uppercase mt-1">Total Requests</div>
      </div>
      <div class="card p-4 text-center">
        <div id="statSuccessRate" class="text-2xl font-extrabold text-mint">—</div>
        <div class="text-[10px] font-bold text-ink/40 uppercase mt-1">Success Rate</div>
      </div>
      <div class="card p-4 text-center">
        <div id="statCost" class="text-2xl font-extrabold text-ink">—</div>
        <div class="text-[10px] font-bold text-ink/40 uppercase mt-1">Est. Cost (USD)</div>
      </div>
      <div class="card p-4 text-center">
        <div id="statRemaining" class="text-2xl font-extrabold text-brand-600">—</div>
        <div class="text-[10px] font-bold text-ink/40 uppercase mt-1">Tokens Remaining</div>
      </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
      {{-- By type breakdown --}}
      <div class="card p-5">
        <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-4">Usage by Feature</div>
        <div id="byType" class="space-y-3"></div>
      </div>

      {{-- By provider breakdown --}}
      <div class="card p-5">
        <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-4">Usage by Provider</div>
        <div id="byProvider" class="space-y-3"></div>
      </div>
    </div>

    {{-- Recent requests --}}
    <div class="card mt-5 overflow-hidden">
      <div class="px-5 py-3 border-b border-line flex items-center justify-between">
        <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Recent Requests</div>
        <div class="flex gap-2">
          <select id="filterProvider" onchange="loadRecent()" class="input text-xs py-1 px-2 h-auto">
            <option value="">All providers</option>
            @foreach($configured as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
          </select>
          <select id="filterType" onchange="loadRecent()" class="input text-xs py-1 px-2 h-auto">
            <option value="">All types</option>
            @foreach(['caption','hashtag','ad_copy','campaign','seo','response','content_ideas','chat'] as $t)
            <option value="{{ $t }}">{{ str_replace('_',' ',ucfirst($t)) }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <table class="w-full text-sm">
        <thead><tr class="text-[10px] font-bold uppercase text-ink/40 border-b border-line">
          <th class="px-5 py-2 text-left">Type</th>
          <th class="px-5 py-2 text-left">Provider</th>
          <th class="px-5 py-2 text-right">Tokens</th>
          <th class="px-5 py-2 text-right">Cost</th>
          <th class="px-5 py-2 text-center">Status</th>
          <th class="px-5 py-2 text-right">Time</th>
        </tr></thead>
        <tbody id="recentTbody" class="divide-y divide-line"></tbody>
      </table>
      <div id="recentEmpty" class="hidden p-8 text-center text-sm text-ink/40">No requests yet this month.</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const statusPill = { completed:'pill-mint', failed:'pill-coral', pending:'pill-gold' };

async function loadUsage() {
  const res  = await fetch('/api/ai/assistant/usage', { credentials:'same-origin' });
  const json = await res.json();
  if (!res.ok) return;

  const d = json.data;
  const pct = d.usage_percent ?? 0;

  document.getElementById('usedTokens').textContent    = (d.tokens_used ?? 0).toLocaleString();
  document.getElementById('limitTokens').textContent   = (d.monthly_limit ?? 0).toLocaleString();
  document.getElementById('usagePct').textContent      = pct.toFixed(1) + '%';
  document.getElementById('resetDate').textContent     = d.resets_on ?? '—';
  document.getElementById('statRequests').textContent  = (d.total_requests ?? 0).toLocaleString();
  document.getElementById('statSuccessRate').textContent = d.success_rate != null ? d.success_rate.toFixed(1) + '%' : '—';
  document.getElementById('statCost').textContent      = d.estimated_cost != null ? '$' + d.estimated_cost.toFixed(4) : '—';
  document.getElementById('statRemaining').textContent = (d.remaining_tokens ?? 0).toLocaleString();

  const bar = document.getElementById('usageBar');
  bar.style.width = Math.min(pct, 100) + '%';
  if (pct >= 90) bar.className = 'h-full rounded-full transition-all duration-700 bg-coral-500';
  else if (pct >= 80) bar.className = 'h-full rounded-full transition-all duration-700 bg-amber-500';

  if (pct >= 80) document.getElementById('limitWarning').classList.remove('hidden');

  // By type
  const byType = d.by_type || {};
  const maxType = Math.max(...Object.values(byType).map(v => v.tokens ?? v), 1);
  document.getElementById('byType').innerHTML = Object.entries(byType).sort((a,b) => (b[1].tokens||b[1]) - (a[1].tokens||a[1])).map(([type, info]) => {
    const tokens = info.tokens ?? info;
    const pctBar = Math.round(tokens / maxType * 100);
    return `<div>
      <div class="flex justify-between text-xs mb-1">
        <span class="font-semibold capitalize text-ink">${type.replace('_',' ')}</span>
        <span class="text-ink/50">${tokens.toLocaleString()} tokens</span>
      </div>
      <div class="h-2 bg-paper rounded-full overflow-hidden">
        <div class="h-full bg-brand-400 rounded-full" style="width:${pctBar}%"></div>
      </div>
    </div>`;
  }).join('') || '<p class="text-sm text-ink/40">No data yet.</p>';

  // By provider
  const byProv = d.by_provider || {};
  const maxProv = Math.max(...Object.values(byProv).map(v => v.tokens ?? v), 1);
  const provColors = { openai:'bg-mint', claude:'bg-coral-400', gemini:'bg-amber-400' };
  document.getElementById('byProvider').innerHTML = Object.entries(byProv).sort((a,b) => (b[1].tokens||b[1]) - (a[1].tokens||a[1])).map(([prov, info]) => {
    const tokens = info.tokens ?? info;
    const pctBar = Math.round(tokens / maxProv * 100);
    const cost   = info.cost != null ? ' · $' + Number(info.cost).toFixed(4) : '';
    return `<div>
      <div class="flex justify-between text-xs mb-1">
        <span class="font-semibold capitalize text-ink">${prov}${cost}</span>
        <span class="text-ink/50">${tokens.toLocaleString()} tokens</span>
      </div>
      <div class="h-2 bg-paper rounded-full overflow-hidden">
        <div class="h-full rounded-full ${provColors[prov] || 'bg-brand-400'}" style="width:${pctBar}%"></div>
      </div>
    </div>`;
  }).join('') || '<p class="text-sm text-ink/40">No data yet.</p>';
}

async function loadRecent() {
  const provider = document.getElementById('filterProvider').value;
  const type     = document.getElementById('filterType').value;
  const params   = new URLSearchParams();
  if (provider) params.set('provider', provider);
  if (type)     params.set('type', type);

  const res  = await fetch('/api/ai/assistant/usage/recent?' + params, { credentials:'same-origin' });
  const json = await res.json();
  const rows = json.data || [];
  const tbody = document.getElementById('recentTbody');

  if (!rows.length) {
    tbody.innerHTML = '';
    document.getElementById('recentEmpty').classList.remove('hidden');
    return;
  }
  document.getElementById('recentEmpty').classList.add('hidden');
  tbody.innerHTML = rows.map(r => `
    <tr class="hover:bg-paper/80">
      <td class="px-5 py-2.5 capitalize text-ink">${(r.type || '').replace('_',' ')}</td>
      <td class="px-5 py-2.5 capitalize text-ink/70">${r.provider || '—'}</td>
      <td class="px-5 py-2.5 text-right text-ink/70">${(r.tokens_used ?? 0).toLocaleString()}</td>
      <td class="px-5 py-2.5 text-right text-ink/70">${r.cost_usd ? '$' + Number(r.cost_usd).toFixed(5) : '—'}</td>
      <td class="px-5 py-2.5 text-center"><span class="pill text-xs ${statusPill[r.status] || ''}">${r.status}</span></td>
      <td class="px-5 py-2.5 text-right text-ink/40 text-xs">${r.created_at_human || r.created_at || ''}</td>
    </tr>
  `).join('');
}

loadUsage();
loadRecent();
</script>
@endpush
