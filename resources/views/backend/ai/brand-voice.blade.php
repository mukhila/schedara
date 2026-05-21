@extends('layouts.backend')
@section('title', 'AI Brand Voice')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Brand Voice</h1>
        <p class="text-ink/60 text-sm">Define your brand's personality so AI always writes in your style.</p>
      </div>
      <button onclick="openModal()" class="bg-brand-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-brand-700 transition-colors text-sm flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        New Voice
      </button>
    </div>

    <div id="voiceGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
    <div id="emptyVoices" class="hidden card p-10 flex flex-col items-center justify-center text-center text-ink/40">
      <svg class="w-10 h-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
      <p class="text-sm">No brand voices yet.</p>
      <p class="text-xs mt-1">Create one or let AI analyze your existing content.</p>
    </div>

    {{-- Analyze from content --}}
    <div class="card p-6 mt-6">
      <h3 class="font-bold text-ink mb-1">Analyze from existing content</h3>
      <p class="text-sm text-ink/60 mb-4">Paste examples of your brand's content and AI will extract your voice automatically.</p>
      <div class="space-y-3">
        <textarea id="analyzeContent" rows="5" class="input" placeholder="Paste 3–5 posts or captions that best represent your brand voice…"></textarea>
        <div class="grid grid-cols-2 gap-3">
          <input type="text" id="analyzeName" class="input" placeholder="Voice name (e.g. Nike Main Brand)">
          <select id="analyzeProvider" class="input">
            <option value="">Default provider</option>
            @foreach($configured as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
          </select>
        </div>
        <button onclick="analyze()" class="bg-ink text-white font-bold px-5 py-2 rounded-lg hover:bg-ink/80 transition-colors text-sm flex items-center gap-2">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          Analyze Brand Voice
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Create/Edit modal --}}
<div id="modal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
  <div class="card w-full max-w-xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between">
      <h2 id="modalTitle" class="font-extrabold text-lg text-ink">New Brand Voice</h2>
      <button onclick="closeModal()" class="text-ink/40 hover:text-ink">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="voiceForm" class="space-y-4">
      <input type="hidden" id="editingId">
      <div><label class="label">Name *</label>
        <input type="text" id="vName" class="input" required placeholder="e.g. Nike — Inspirational"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="label">Tone</label>
          <input type="text" id="vTone" class="input" placeholder="e.g. Inspirational, bold"></div>
        <div><label class="label">Personality</label>
          <input type="text" id="vPersonality" class="input" placeholder="e.g. Confident, empowering"></div>
      </div>
      <div><label class="label">Writing style</label>
        <input type="text" id="vStyle" class="input" placeholder="e.g. Short punchy sentences, action verbs"></div>
      <div><label class="label">Target audience</label>
        <input type="text" id="vAudience" class="input" placeholder="e.g. Athletes 18-35, high achievers"></div>
      <div><label class="label">Words / phrases to use</label>
        <textarea id="vInclude" rows="2" class="input" placeholder="e.g. Just do it, push limits, champion…"></textarea></div>
      <div><label class="label">Words / phrases to avoid</label>
        <textarea id="vExclude" rows="2" class="input" placeholder="e.g. cheap, discount, maybe…"></textarea></div>
      <div><label class="label">Example copy</label>
        <textarea id="vExample" rows="3" class="input" placeholder="Paste an example post that perfectly represents your brand voice…"></textarea></div>
      <div class="flex items-center gap-2">
        <input type="checkbox" id="vDefault" class="rounded border-line">
        <label for="vDefault" class="text-sm font-semibold text-ink cursor-pointer">Set as default brand voice</label>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-semibold text-ink/60 hover:text-ink">Cancel</button>
        <button type="submit" class="bg-brand-600 text-white font-bold px-5 py-2 rounded-lg hover:bg-brand-700 text-sm">Save Voice</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
let voices = [];

async function load() {
  const res  = await fetch('/api/ai/assistant/brand-voices', { credentials:'same-origin' });
  const json = await res.json();
  voices     = json.data || [];
  render();
}

function render() {
  const grid = document.getElementById('voiceGrid');
  if (!voices.length) {
    grid.innerHTML = '';
    document.getElementById('emptyVoices').classList.remove('hidden');
    return;
  }
  document.getElementById('emptyVoices').classList.add('hidden');
  grid.innerHTML = voices.map(v => `
    <div class="card p-5 space-y-3 relative">
      ${v.is_default ? '<span class="absolute top-3 right-3 pill pill-brand text-[10px]">Default</span>' : ''}
      <h4 class="font-bold text-ink pr-16">${escHtml(v.name)}</h4>
      ${v.tone ? `<div class="flex gap-2 flex-wrap">
        ${v.tone.split(',').map(t => `<span class="pill pill-gold text-xs">${escHtml(t.trim())}</span>`).join('')}
      </div>` : ''}
      ${v.personality ? `<p class="text-xs text-ink/60">${escHtml(v.personality)}</p>` : ''}
      ${v.example_copy ? `<blockquote class="border-l-2 border-brand-300 pl-3 text-xs text-ink/60 italic line-clamp-3">${escHtml(v.example_copy)}</blockquote>` : ''}
      <div class="flex items-center justify-between pt-2 border-t border-line">
        ${!v.is_default ? `<button onclick="setDefault(${v.id})" class="text-xs font-bold text-ink/40 hover:text-ink">Set default</button>` : '<span></span>'}
        <div class="flex gap-3">
          <button onclick="editVoice(${v.id})" class="text-xs font-bold text-ink/50 hover:text-ink">Edit</button>
          <button onclick="deleteVoice(${v.id})" class="text-xs font-bold text-coral-500 hover:text-coral-700">Delete</button>
        </div>
      </div>
    </div>
  `).join('');
}

function openModal(voice = null) {
  document.getElementById('editingId').value    = voice?.id || '';
  document.getElementById('vName').value        = voice?.name || '';
  document.getElementById('vTone').value        = voice?.tone || '';
  document.getElementById('vPersonality').value = voice?.personality || '';
  document.getElementById('vStyle').value       = voice?.writing_style || '';
  document.getElementById('vAudience').value    = voice?.target_audience || '';
  document.getElementById('vInclude').value     = (voice?.words_to_use || []).join(', ');
  document.getElementById('vExclude').value     = (voice?.words_to_avoid || []).join(', ');
  document.getElementById('vExample').value     = voice?.example_copy || '';
  document.getElementById('vDefault').checked   = voice?.is_default || false;
  document.getElementById('modalTitle').textContent = voice ? 'Edit Brand Voice' : 'New Brand Voice';
  document.getElementById('modal').classList.remove('hidden');
}

function closeModal() { document.getElementById('modal').classList.add('hidden'); }

function editVoice(id) {
  const v = voices.find(v => v.id === id);
  if (v) openModal(v);
}

async function deleteVoice(id) {
  if (!confirm('Delete this brand voice?')) return;
  await fetch(`/api/ai/assistant/brand-voices/${id}`, {
    method:'DELETE',
    headers:{ 'X-Requested-With':'XMLHttpRequest' },
    credentials:'same-origin'
  });
  load();
}

async function setDefault(id) {
  await fetch(`/api/ai/assistant/brand-voices/${id}`, {
    method:'PUT',
    headers:{ 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' },
    credentials:'same-origin',
    body: JSON.stringify({ is_default: true }),
  });
  load();
}

document.getElementById('voiceForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const id   = document.getElementById('editingId').value;
  const body = {
    name:           document.getElementById('vName').value,
    tone:           document.getElementById('vTone').value,
    personality:    document.getElementById('vPersonality').value,
    writing_style:  document.getElementById('vStyle').value,
    target_audience:document.getElementById('vAudience').value,
    words_to_use:   document.getElementById('vInclude').value.split(',').map(s=>s.trim()).filter(Boolean),
    words_to_avoid: document.getElementById('vExclude').value.split(',').map(s=>s.trim()).filter(Boolean),
    example_copy:   document.getElementById('vExample').value,
    is_default:     document.getElementById('vDefault').checked,
  };
  const url    = id ? `/api/ai/assistant/brand-voices/${id}` : '/api/ai/assistant/brand-voices';
  const method = id ? 'PUT' : 'POST';
  await fetch(url, {
    method, credentials:'same-origin',
    headers:{ 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' },
    body: JSON.stringify(body),
  });
  closeModal();
  load();
});

async function analyze() {
  const content  = document.getElementById('analyzeContent').value.trim();
  const name     = document.getElementById('analyzeName').value.trim();
  const provider = document.getElementById('analyzeProvider').value;
  if (!content) { alert('Paste some content first.'); return; }
  if (!name)    { alert('Enter a name for this voice.'); return; }

  const btn = document.querySelector('[onclick="analyze()"]');
  btn.textContent = 'Analyzing…'; btn.disabled = true;

  const res  = await fetch('/api/ai/assistant/brand-voices/analyze', {
    method:'POST',
    headers:{ 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' },
    credentials:'same-origin',
    body: JSON.stringify({ content, name, provider }),
  });
  const json = await res.json();
  btn.innerHTML = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Analyze Brand Voice';
  btn.disabled = false;

  if (!res.ok) { alert(json.error); return; }

  openModal({ ...json.data, name });
}

document.getElementById('modal').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeModal();
});

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

load();
</script>
@endpush
