@extends('layouts.backend')
@section('title', 'AI Ad Copy Generator')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Ad Copy Generator</h1>
    <p class="text-ink/60 text-sm mb-6">Generate high-converting ad copy for Facebook, Google, LinkedIn, and more.</p>
    <div class="grid lg:grid-cols-[340px_1fr] gap-5">
      <div class="card p-6">
        <form id="adForm" class="space-y-4">
          <div><label class="label">Product / Service *</label>
            <input type="text" name="product" class="input" placeholder="e.g. Premium running shoes" required></div>
          <div><label class="label">Value proposition *</label>
            <textarea name="value_proposition" rows="3" class="input" placeholder="What makes it unique? Key benefits…" required></textarea></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Platform</label>
              <select name="platform" class="input">
                <option value="facebook">Facebook</option><option value="instagram">Instagram</option>
                <option value="google">Google</option><option value="linkedin">LinkedIn</option>
                <option value="tiktok">TikTok</option><option value="twitter">Twitter/X</option>
              </select></div>
            <div><label class="label">Goal</label>
              <select name="goal" class="input">
                <option value="conversions">Conversions</option><option value="awareness">Awareness</option>
                <option value="traffic">Traffic</option><option value="leads">Leads</option>
              </select></div>
          </div>
          <div><label class="label">Target audience</label>
            <input type="text" name="audience" class="input" placeholder="e.g. Athletes 18-35, fitness enthusiasts"></div>
          <div><label class="label">Tone</label>
            <select name="tone" class="input">
              <option value="persuasive">Persuasive</option><option value="urgent">Urgent</option>
              <option value="friendly">Friendly</option><option value="professional">Professional</option>
              <option value="bold">Bold</option>
            </select></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Budget hint</label>
              <input type="text" name="budget" class="input" placeholder="e.g. $500/mo"></div>
            <div><label class="label">Variations</label>
              <select name="variations" class="input">
                <option value="2">2</option><option value="3" selected>3</option><option value="5">5</option>
              </select></div>
          </div>
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
              <option value="">Default</option>
              @foreach($configured as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
            </select></div>
          <button type="submit" class="w-full bg-brand-600 text-white font-bold py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Generate Ad Copy</button>
        </form>
      </div>

      <div id="results" class="hidden space-y-4"></div>
      <div id="emptyState" class="card p-10 flex flex-col items-center justify-center text-center text-ink/40">
        <svg class="w-10 h-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 10l4.553-2.069A1 1 0 0121 8.87V15.13a1 1 0 01-1.447.899L15 14M3 8a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
        <p class="text-sm">Fill in the form and click Generate.</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('adForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  e.submitter.textContent = 'Generating…'; e.submitter.disabled = true;

  const res  = await fetch('/api/ai/assistant/ad-copy', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  const json = await res.json();
  e.submitter.textContent = 'Generate Ad Copy'; e.submitter.disabled = false;
  if (!res.ok) { alert(json.error); return; }

  const ads = json.data.variations || [];
  const r = document.getElementById('results');
  r.innerHTML = ads.map((ad, i) => `
    <div class="card p-5 space-y-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-ink/50 uppercase tracking-wider">Variation ${i + 1}</span>
        ${ad.estimated_ctr ? `<span class="pill pill-mint text-xs">Est. CTR ${ad.estimated_ctr}</span>` : ''}
      </div>
      ${ad.headline ? `
      <div>
        <div class="text-[10px] font-bold text-ink/40 uppercase mb-1">Headline</div>
        <p class="font-bold text-ink">${escHtml(ad.headline)}</p>
        <button onclick="copy(this,'${escAttr(ad.headline)}')" class="mt-1 text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
      </div>` : ''}
      ${ad.primary_text ? `
      <div>
        <div class="text-[10px] font-bold text-ink/40 uppercase mb-1">Primary Text</div>
        <p class="text-sm text-ink">${escHtml(ad.primary_text)}</p>
        <button onclick="copy(this,'${escAttr(ad.primary_text)}')" class="mt-1 text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
      </div>` : ''}
      ${ad.description ? `
      <div>
        <div class="text-[10px] font-bold text-ink/40 uppercase mb-1">Description</div>
        <p class="text-sm text-ink/80">${escHtml(ad.description)}</p>
        <button onclick="copy(this,'${escAttr(ad.description)}')" class="mt-1 text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
      </div>` : ''}
      ${ad.cta ? `
      <div class="pt-3 border-t border-line flex items-center gap-2">
        <span class="text-[10px] font-bold text-ink/40 uppercase">CTA</span>
        <span class="pill pill-brand text-xs">${escHtml(ad.cta)}</span>
      </div>` : ''}
    </div>
  `).join('');

  document.getElementById('emptyState').classList.add('hidden');
  r.classList.remove('hidden');
});

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escAttr(s) { return String(s).replace(/'/g,'&#39;').replace(/"/g,'&quot;'); }
function copy(btn, text) {
  navigator.clipboard.writeText(text).then(() => {
    const o = btn.textContent; btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = o, 1500);
  });
}
</script>
@endpush
