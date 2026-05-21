@extends('layouts.backend')
@section('title', 'AI Content Ideas')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Content Ideas Generator</h1>
    <p class="text-ink/60 text-sm mb-6">Get viral hooks, post formats, and a ready-to-use content calendar.</p>
    <div class="grid lg:grid-cols-[340px_1fr] gap-5">
      <div class="card p-6">
        <form id="ideasForm" class="space-y-4">
          <div><label class="label">Industry *</label>
            <input type="text" name="industry" class="input" placeholder="e.g. fashion, SaaS, fitness" required></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Platform</label>
              <select name="platform" class="input">
                <option value="instagram">Instagram</option><option value="tiktok">TikTok</option>
                <option value="linkedin">LinkedIn</option><option value="youtube">YouTube</option><option value="all">All</option>
              </select></div>
            <div><label class="label">Format</label>
              <select name="period" class="input">
                <option value="week">Weekly</option><option value="day">Daily</option><option value="month">Monthly</option>
              </select></div>
          </div>
          <div><label class="label">Tone</label>
            <select name="tone" class="input">
              <option value="professional">Professional</option><option value="viral">Viral</option>
              <option value="funny">Funny</option><option value="educational">Educational</option>
            </select></div>
          <div><label class="label">Goal</label>
            <input type="text" name="goal" class="input" placeholder="e.g. brand awareness, leads"></div>
          <div><label class="label">Count</label>
            <select name="count" class="input">
              <option value="5">5 ideas</option><option value="10" selected>10 ideas</option><option value="15">15 ideas</option>
            </select></div>
          <div><label class="label">AI Provider</label>
            <select name="provider" class="input">
              <option value="">Default</option>
              @foreach($configured as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
            </select></div>
          <button type="submit" class="w-full bg-brand-600 text-white font-bold py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Generate Ideas</button>
        </form>
      </div>
      <div id="results" class="hidden space-y-3"></div>
      <div id="emptyState" class="card p-10 flex flex-col items-center justify-center text-center text-ink/40">
        <svg class="w-10 h-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707"/></svg>
        <p class="text-sm">Enter your industry to spark ideas.</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const fmtColors = { reel:'pill-coral', carousel:'pill-brand', static:'bg-line text-ink/70', story:'pill-gold', live:'pill-mint' };
const engColors = { high:'pill-mint', medium:'pill-gold', low:'pill-coral' };

document.getElementById('ideasForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  e.submitter.textContent = 'Generating…'; e.submitter.disabled = true;

  const res  = await fetch('/api/ai/assistant/content-ideas', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  const json = await res.json();
  e.submitter.textContent = 'Generate Ideas'; e.submitter.disabled = false;
  if (!res.ok) { alert(json.error); return; }

  const ideas = json.data.ideas || [];
  const r = document.getElementById('results');
  r.innerHTML = ideas.map((idea, i) => `
    <div class="card p-5">
      <div class="flex items-start justify-between gap-3 mb-2">
        <h4 class="font-bold text-ink">${i + 1}. ${escHtml(idea.title || '')}</h4>
        <div class="flex gap-1.5 flex-shrink-0">
          <span class="pill text-xs ${fmtColors[idea.format] || 'pill'}">${idea.format}</span>
          <span class="pill text-xs ${engColors[idea.estimated_engagement] || ''}">${idea.estimated_engagement} eng.</span>
        </div>
      </div>
      <p class="text-sm text-ink/70 mb-3">${escHtml(idea.description || '')}</p>
      <div class="grid grid-cols-2 gap-3 text-xs">
        <div><span class="font-bold text-ink/50 uppercase tracking-wider">Hook</span><p class="mt-1 text-ink">${escHtml(idea.hook || '')}</p></div>
        <div><span class="font-bold text-ink/50 uppercase tracking-wider">CTA</span><p class="mt-1 text-ink">${escHtml(idea.cta || '')}</p></div>
      </div>
    </div>
  `).join('');

  document.getElementById('emptyState').classList.add('hidden');
  r.classList.remove('hidden');
});

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
@endpush
