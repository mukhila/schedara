@extends('layouts.backend')
@section('title', 'AI Campaign Builder')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Campaign Builder</h1>
    <p class="text-ink/60 text-sm mb-6">Generate a complete multi-platform campaign strategy with posts, captions, and a content calendar.</p>
    <div class="grid lg:grid-cols-[340px_1fr] gap-5">
      <div class="card p-6">
        <form id="campaignForm" class="space-y-4">
          <div><label class="label">Campaign name *</label>
            <input type="text" name="name" class="input" placeholder="e.g. Summer Sale 2024" required></div>
          <div><label class="label">Campaign goal *</label>
            <textarea name="goal" rows="2" class="input" placeholder="e.g. Drive 20% more sales of summer collection" required></textarea></div>
          <div><label class="label">Product / Service</label>
            <input type="text" name="product" class="input" placeholder="e.g. Nike Air Max 2024"></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Duration</label>
              <select name="duration" class="input">
                <option value="1 week">1 week</option><option value="2 weeks">2 weeks</option>
                <option value="1 month" selected>1 month</option><option value="3 months">3 months</option>
              </select></div>
            <div><label class="label">Budget</label>
              <input type="text" name="budget" class="input" placeholder="e.g. $2,000"></div>
          </div>
          <div><label class="label">Platforms</label>
            <div class="grid grid-cols-2 gap-2 mt-1">
              @foreach(['Instagram','Facebook','LinkedIn','TikTok','Twitter/X','YouTube'] as $pl)
              <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="platforms[]" value="{{ strtolower(str_replace('/','',$pl)) }}" class="rounded border-line" {{ in_array($pl,['Instagram','Facebook']) ? 'checked' : '' }}>
                {{ $pl }}
              </label>
              @endforeach
            </div></div>
          <div><label class="label">Target audience</label>
            <input type="text" name="audience" class="input" placeholder="e.g. Women 25-40 interested in fitness"></div>
          <div><label class="label">Tone</label>
            <select name="tone" class="input">
              <option value="exciting">Exciting</option><option value="professional">Professional</option>
              <option value="playful">Playful</option><option value="urgent">Urgent</option><option value="inspirational">Inspirational</option>
            </select></div>
          @if($brandVoices->count() > 0)
          <div><label class="label">Brand Voice</label>
            <select name="brand_voice_id" class="input">
              <option value="">None</option>
              @foreach($brandVoices as $v)
              <option value="{{ $v->uuid }}" {{ $v->is_default ? 'selected' : '' }}>{{ $v->name }}</option>
              @endforeach
            </select></div>
          @endif
          <div><label class="label">AI Provider</label>
            <select name="provider" class="input">
              <option value="">Default (Claude)</option>
              @foreach($configured as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
            </select></div>
          <button type="submit" class="w-full bg-brand-600 text-white font-bold py-2.5 rounded-lg hover:bg-brand-700 transition-colors flex items-center justify-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Build Campaign
          </button>
        </form>
      </div>

      <div id="results" class="hidden space-y-5">
        {{-- Overview card --}}
        <div class="card p-5">
          <div class="flex items-start justify-between gap-4 mb-4">
            <div>
              <h3 id="campName" class="font-extrabold text-lg text-ink"></h3>
              <p id="campSummary" class="text-sm text-ink/60 mt-1"></p>
            </div>
            <span id="campDuration" class="pill pill-brand text-xs flex-shrink-0"></span>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-3 bg-paper rounded-lg">
              <div id="campPosts" class="text-2xl font-extrabold text-ink">—</div>
              <div class="text-[10px] font-bold text-ink/40 uppercase mt-1">Total Posts</div>
            </div>
            <div class="text-center p-3 bg-paper rounded-lg">
              <div id="campPlatforms" class="text-2xl font-extrabold text-ink">—</div>
              <div class="text-[10px] font-bold text-ink/40 uppercase mt-1">Platforms</div>
            </div>
            <div class="text-center p-3 bg-paper rounded-lg">
              <div id="campBudget" class="text-2xl font-extrabold text-ink">—</div>
              <div class="text-[10px] font-bold text-ink/40 uppercase mt-1">Budget</div>
            </div>
          </div>
        </div>

        {{-- Content calendar --}}
        <div class="card p-5">
          <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">Content Calendar</div>
          <div id="calendar" class="space-y-2"></div>
        </div>

        {{-- Key messages --}}
        <div class="card p-5">
          <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">Key Messages</div>
          <ul id="keyMessages" class="space-y-2 text-sm"></ul>
        </div>

        {{-- KPIs --}}
        <div class="card p-5">
          <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">Target KPIs</div>
          <ul id="kpis" class="space-y-2 text-sm"></ul>
        </div>
      </div>

      <div id="emptyState" class="card p-10 flex flex-col items-center justify-center text-center text-ink/40">
        <svg class="w-10 h-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="text-sm">Fill in the form to build your campaign.</p>
        <p class="text-xs mt-1 text-ink/30">Campaign generation may take 15–30 seconds.</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const platformColors = {
  instagram:'pill-coral', facebook:'pill-brand', linkedin:'pill-mint',
  tiktok:'pill-gold', twitter:'bg-line text-ink/70', twitterx:'bg-line text-ink/70', youtube:'pill-coral'
};

document.getElementById('campaignForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const fd = new FormData(e.target);
  const payload = Object.fromEntries(fd);
  payload.platforms = fd.getAll('platforms[]');

  const btn = e.submitter;
  btn.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Building…';
  btn.disabled = true;

  const res  = await fetch('/api/ai/assistant/campaign', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  const json = await res.json();
  btn.innerHTML = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Build Campaign';
  btn.disabled = false;
  if (!res.ok) { alert(json.error); return; }

  const d = json.data;

  document.getElementById('campName').textContent      = d.name || payload.name;
  document.getElementById('campSummary').textContent   = d.summary || '';
  document.getElementById('campDuration').textContent  = d.duration || payload.duration;
  document.getElementById('campPosts').textContent     = d.total_posts ?? (d.schedule?.length ?? '—');
  document.getElementById('campPlatforms').textContent = (d.platforms || payload.platforms).length;
  document.getElementById('campBudget').textContent    = d.budget_allocation || payload.budget || '—';

  document.getElementById('calendar').innerHTML = (d.schedule || []).map(item => `
    <div class="flex items-start gap-3 p-3 bg-paper rounded-lg">
      <div class="text-[10px] font-bold text-ink/40 uppercase w-20 flex-shrink-0 pt-0.5">${escHtml(item.day || item.date || '')}</div>
      <div class="flex-1">
        <p class="text-sm font-semibold text-ink">${escHtml(item.content || item.post_idea || '')}</p>
        <div class="flex gap-1.5 mt-1 flex-wrap">
          ${(item.platforms || [item.platform]).filter(Boolean).map(p => `<span class="pill text-xs ${platformColors[p] || ''}">${p}</span>`).join('')}
          ${item.format ? `<span class="pill text-xs bg-line text-ink/60">${item.format}</span>` : ''}
        </div>
      </div>
    </div>
  `).join('');

  document.getElementById('keyMessages').innerHTML = (d.key_messages || []).map(m =>
    `<li class="flex gap-2"><span class="text-brand-500 flex-shrink-0">→</span>${escHtml(m)}</li>`
  ).join('');

  document.getElementById('kpis').innerHTML = (d.kpis || []).map(k =>
    `<li class="flex gap-2"><span class="text-mint flex-shrink-0">✓</span>${escHtml(k)}</li>`
  ).join('');

  document.getElementById('emptyState').classList.add('hidden');
  document.getElementById('results').classList.remove('hidden');
});

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
@endpush
