@extends('layouts.backend')
@section('title', 'AI Caption Generator')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>

  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Caption Generator</h1>
    <p class="text-ink/60 text-sm mb-6">Generate platform-optimised captions in any tone using AI.</p>

    <div class="grid lg:grid-cols-[1fr_1.2fr] gap-5">

      {{-- Form --}}
      <div class="card p-6">
        <form id="captionForm" class="space-y-4">
          <div>
            <label class="label">Topic / Product *</label>
            <input type="text" name="topic" placeholder="e.g. New summer sneaker collection" class="input" required>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Platform *</label>
              <select name="platform" class="input">
                <option value="instagram">Instagram</option>
                <option value="facebook">Facebook</option>
                <option value="twitter">Twitter/X</option>
                <option value="linkedin">LinkedIn</option>
                <option value="tiktok">TikTok</option>
                <option value="youtube">YouTube</option>
                <option value="pinterest">Pinterest</option>
              </select>
            </div>
            <div>
              <label class="label">Tone *</label>
              <select name="tone" class="input">
                <option value="professional">Professional</option>
                <option value="viral">Viral</option>
                <option value="funny">Funny</option>
                <option value="emotional">Emotional</option>
                <option value="luxury">Luxury</option>
                <option value="educational">Educational</option>
                <option value="product">Product-focused</option>
              </select>
            </div>
          </div>
          <div>
            <label class="label">Brand name</label>
            <input type="text" name="brand" placeholder="Your brand name" class="input">
          </div>
          <div>
            <label class="label">Target audience</label>
            <input type="text" name="audience" placeholder="e.g. Women 25-35 interested in fitness" class="input">
          </div>
          <div>
            <label class="label">Keywords (comma-separated)</label>
            <input type="text" name="keywords" placeholder="e.g. sustainable, trendy, comfort" class="input">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Variations</label>
              <select name="count" class="input">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3" selected>3</option>
                <option value="5">5</option>
              </select>
            </div>
            <div>
              <label class="label">AI Provider</label>
              <select name="provider" class="input">
                <option value="">Default ({{ config('ai.default_provider') }})</option>
                @foreach($configured as $p)
                  <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                @endforeach
              </select>
            </div>
          </div>
          @if($brandVoices->count() > 0)
          <div>
            <label class="label">Brand Voice</label>
            <select name="brand_voice_id" class="input">
              <option value="">None</option>
              @foreach($brandVoices as $v)
              <option value="{{ $v->uuid }}" {{ $v->is_default ? 'selected' : '' }}>{{ $v->name }}</option>
              @endforeach
            </select>
          </div>
          @endif
          <button type="submit" id="generateBtn"
            class="w-full bg-brand-600 text-white font-bold py-2.5 rounded-lg hover:bg-brand-700 transition-colors flex items-center justify-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 3.9 2.4-7.4L2 9.4h7.6z"/></svg>
            <span id="btnText">Generate Captions</span>
          </button>
        </form>
      </div>

      {{-- Results --}}
      <div id="results" class="space-y-4 hidden">
        <div class="flex items-center justify-between">
          <h3 class="font-bold text-ink">Generated Captions</h3>
          <span id="providerBadge" class="pill pill-brand text-xs"></span>
        </div>
        <div id="captionCards" class="space-y-4"></div>
      </div>

      {{-- Empty state --}}
      <div id="emptyState" class="card p-10 flex flex-col items-center justify-center text-center text-ink/40">
        <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3l-4 4z"/></svg>
        <p class="text-sm">Fill in the form and click Generate to create your captions.</p>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const form    = document.getElementById('captionForm');
const results = document.getElementById('results');
const empty   = document.getElementById('emptyState');
const cards   = document.getElementById('captionCards');
const btn     = document.getElementById('generateBtn');
const btnText = document.getElementById('btnText');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  btn.disabled = true;
  btnText.textContent = 'Generating…';

  const payload = Object.fromEntries(new FormData(form));

  try {
    const res = await fetch('/api/ai/assistant/caption', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    });

    const json = await res.json();
    if (!res.ok) { alert(json.error || 'Generation failed'); return; }

    const { captions, provider, model } = json.data;

    document.getElementById('providerBadge').textContent = `${provider} · ${model}`;

    cards.innerHTML = (captions || []).map((c, i) => `
      <div class="card p-5 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-ink/50 uppercase tracking-wider">Variation ${i + 1} · ${c.tone_used || ''}</span>
        </div>
        <div>
          <div class="text-[10px] font-bold text-ink/40 uppercase mb-1">Short</div>
          <p class="text-sm text-ink">${escHtml(c.short || '')}</p>
          <button onclick="copy(this, \`${escJs(c.short || '')}\`)" class="mt-1 text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
        </div>
        <div>
          <div class="text-[10px] font-bold text-ink/40 uppercase mb-1">Long</div>
          <p class="text-sm text-ink">${escHtml(c.long || '')}</p>
          <button onclick="copy(this, \`${escJs(c.long || '')}\`)" class="mt-1 text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
        </div>
        <div>
          <div class="text-[10px] font-bold text-ink/40 uppercase mb-1">With Emojis</div>
          <p class="text-sm text-ink">${escHtml(c.emoji_version || '')}</p>
          <button onclick="copy(this, \`${escJs(c.emoji_version || '')}\`)" class="mt-1 text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
        </div>
        <div class="pt-3 border-t border-line">
          <div class="text-[10px] font-bold text-ink/40 uppercase mb-1">CTA</div>
          <p class="text-xs font-semibold text-brand-700">${escHtml(c.cta || '')}</p>
        </div>
        ${c.hashtags ? `<div class="text-xs text-ink/50">${(c.hashtags || []).join(' ')}</div>` : ''}
      </div>
    `).join('');

    empty.classList.add('hidden');
    results.classList.remove('hidden');
  } catch (err) {
    alert('Request failed. Check your connection.');
  } finally {
    btn.disabled = false;
    btnText.textContent = 'Generate Captions';
  }
});

function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escJs(s)   { return s.replace(/`/g,'\\`').replace(/\$/g,'\\$'); }

function copy(btn, text) {
  navigator.clipboard.writeText(text).then(() => {
    const orig = btn.textContent;
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = orig, 1500);
  });
}
</script>
@endpush
