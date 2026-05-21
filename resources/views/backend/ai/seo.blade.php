@extends('layouts.backend')
@section('title', 'AI SEO Optimizer')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">SEO Optimizer</h1>
    <p class="text-ink/60 text-sm mb-6">Score and optimize captions, descriptions, and ad copy for better discovery.</p>
    <div class="grid lg:grid-cols-[1fr_1fr] gap-5">
      <div class="card p-6">
        <form id="seoForm" class="space-y-4">
          <div><label class="label">Content to optimize *</label>
            <textarea name="content" rows="5" class="input" placeholder="Paste your caption, description, or ad copy here…" required></textarea></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Platform</label>
              <select name="platform" class="input">
                <option value="instagram">Instagram</option><option value="youtube">YouTube</option>
                <option value="linkedin">LinkedIn</option><option value="blog">Blog</option><option value="general">General</option>
              </select></div>
            <div><label class="label">Content type</label>
              <select name="type" class="input">
                <option value="caption">Caption</option><option value="description">Description</option>
                <option value="blog">Blog post</option><option value="ad_copy">Ad copy</option>
              </select></div>
          </div>
          <div><label class="label">Target keywords</label>
            <input type="text" name="keywords" class="input" placeholder="e.g. running shoes, buy online, best 2024"></div>
          <div><label class="label">AI Provider</label>
            <select name="provider" class="input">
              <option value="">Default</option>
              @foreach($configured as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
            </select></div>
          <button type="submit" class="w-full bg-brand-600 text-white font-bold py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Analyze & Optimize</button>
        </form>
      </div>

      <div id="results" class="space-y-4 hidden">
        {{-- Score gauges --}}
        <div class="card p-5">
          <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
              <div id="seoScore" class="text-4xl font-extrabold text-brand-600">—</div>
              <div class="text-xs font-bold uppercase text-ink/50 mt-1">SEO Score</div>
            </div>
            <div class="text-center">
              <div id="readScore" class="text-4xl font-extrabold text-mint">—</div>
              <div class="text-xs font-bold uppercase text-ink/50 mt-1">Readability</div>
            </div>
          </div>
        </div>
        <div class="card p-5">
          <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-2">Optimized Content</div>
          <p id="optimizedContent" class="text-sm text-ink whitespace-pre-wrap leading-relaxed"></p>
          <button id="copyOptimized" class="mt-2 text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
        </div>
        <div class="card p-5">
          <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-2">Meta Description</div>
          <p id="metaDesc" class="text-sm text-ink/80"></p>
          <button id="copyMeta" class="mt-1 text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
        </div>
        <div class="card p-5">
          <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">Recommendations</div>
          <ul id="recommendations" class="space-y-2 text-sm"></ul>
        </div>
      </div>
      <div id="emptyState" class="card p-10 flex flex-col items-center justify-center text-center text-ink/40">
        <svg class="w-10 h-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <p class="text-sm">Paste content and click Analyze.</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('seoForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  e.submitter.textContent = 'Analyzing…'; e.submitter.disabled = true;

  const res  = await fetch('/api/ai/assistant/seo-optimize', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  const json = await res.json();
  e.submitter.textContent = 'Analyze & Optimize'; e.submitter.disabled = false;
  if (!res.ok) { alert(json.error); return; }

  const d = json.data;
  document.getElementById('seoScore').textContent       = d.seo_score ?? '—';
  document.getElementById('readScore').textContent      = d.readability_score ?? '—';
  document.getElementById('optimizedContent').textContent = d.optimized_content ?? '';
  document.getElementById('metaDesc').textContent       = d.meta_description ?? '';
  document.getElementById('recommendations').innerHTML  = (d.recommendations || []).map(r =>
    `<li class="flex gap-2"><span class="text-brand-500 flex-shrink-0">→</span>${escHtml(r)}</li>`
  ).join('');

  const oc = document.getElementById('optimizedContent').textContent;
  document.getElementById('copyOptimized').onclick = () => navigator.clipboard.writeText(oc);
  document.getElementById('copyMeta').onclick = () => navigator.clipboard.writeText(document.getElementById('metaDesc').textContent);

  document.getElementById('emptyState').classList.add('hidden');
  document.getElementById('results').classList.remove('hidden');
});

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
@endpush
