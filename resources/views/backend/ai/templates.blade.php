@extends('layouts.backend')
@section('title', 'AI Prompt Templates')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Prompt Templates</h1>
        <p class="text-ink/60 text-sm">Save and reuse your best AI prompts.</p>
      </div>
      <button onclick="openModal()" class="bg-brand-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-brand-700 transition-colors text-sm flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        New Template
      </button>
    </div>

    {{-- Filter bar --}}
    <div class="flex flex-wrap gap-2 mb-5">
      <button onclick="filterType(null)" id="filter-all" class="pill pill-brand text-xs">All</button>
      @foreach(['caption','hashtag','ad_copy','campaign','seo','response','content_ideas'] as $type)
      <button onclick="filterType('{{ $type }}')" id="filter-{{ $type }}" class="pill text-xs bg-line text-ink/70">{{ str_replace('_',' ',ucfirst($type)) }}</button>
      @endforeach
    </div>

    {{-- Template grid --}}
    <div id="templateGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
    <div id="emptyTemplates" class="hidden card p-10 flex flex-col items-center justify-center text-center text-ink/40">
      <svg class="w-10 h-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      <p class="text-sm">No templates yet. Create your first one.</p>
    </div>
  </div>
</div>

{{-- Create / Edit modal --}}
<div id="modal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
  <div class="card w-full max-w-lg p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="font-extrabold text-lg text-ink" id="modalTitle">New Template</h2>
      <button onclick="closeModal()" class="text-ink/40 hover:text-ink">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="templateForm" class="space-y-4">
      <input type="hidden" id="editingId">
      <div><label class="label">Template name *</label>
        <input type="text" id="tName" class="input" placeholder="e.g. Product launch caption" required></div>
      <div><label class="label">Type</label>
        <select id="tType" class="input">
          <option value="caption">Caption</option><option value="hashtag">Hashtag</option>
          <option value="ad_copy">Ad Copy</option><option value="campaign">Campaign</option>
          <option value="seo">SEO</option><option value="response">Response</option>
          <option value="content_ideas">Content Ideas</option><option value="general">General</option>
        </select></div>
      <div><label class="label">Prompt body *</label>
        <textarea id="tBody" rows="6" class="input font-mono text-xs" placeholder="Write your prompt here. Use {variable_name} for dynamic parts…" required></textarea>
        <p class="text-[10px] text-ink/40 mt-1">Use <code class="bg-paper px-1 rounded">{variable}</code> syntax for placeholders. Example: Write a caption for {product} in a {tone} tone.</p>
      </div>
      <div><label class="label">Description</label>
        <input type="text" id="tDescription" class="input" placeholder="Short note about when to use this template"></div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-semibold text-ink/60 hover:text-ink">Cancel</button>
        <button type="submit" class="bg-brand-600 text-white font-bold px-5 py-2 rounded-lg hover:bg-brand-700 text-sm">Save</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
let templates = [];
let activeType = null;
const typeColors = {
  caption:'pill-brand', hashtag:'pill-mint', ad_copy:'pill-coral', campaign:'pill-gold',
  seo:'bg-line text-ink/70', response:'pill-brand', content_ideas:'pill-mint', general:'bg-line text-ink/60'
};

async function load() {
  const res  = await fetch('/api/ai/assistant/templates', { credentials:'same-origin' });
  const json = await res.json();
  templates  = json.data || [];
  render();
}

function render() {
  const list = activeType ? templates.filter(t => t.type === activeType) : templates;
  const grid = document.getElementById('templateGrid');
  if (!list.length) {
    grid.innerHTML = '';
    document.getElementById('emptyTemplates').classList.remove('hidden');
    return;
  }
  document.getElementById('emptyTemplates').classList.add('hidden');
  grid.innerHTML = list.map(t => `
    <div class="card p-5 flex flex-col gap-3">
      <div class="flex items-start justify-between gap-2">
        <h4 class="font-bold text-ink text-sm leading-snug">${escHtml(t.name)}</h4>
        <span class="pill text-xs ${typeColors[t.type] || ''} flex-shrink-0">${t.type.replace('_',' ')}</span>
      </div>
      ${t.description ? `<p class="text-xs text-ink/60">${escHtml(t.description)}</p>` : ''}
      <pre class="text-[11px] bg-paper rounded-lg p-3 text-ink/70 overflow-x-auto whitespace-pre-wrap font-mono line-clamp-4">${escHtml(t.body)}</pre>
      <div class="flex items-center justify-between pt-1">
        <span class="text-[10px] text-ink/40">${t.use_count || 0} uses</span>
        <div class="flex gap-3">
          ${!t.is_system ? `
          <button onclick="editTemplate(${t.id})" class="text-xs font-bold text-ink/50 hover:text-ink">Edit</button>
          <button onclick="deleteTemplate(${t.id})" class="text-xs font-bold text-coral-500 hover:text-coral-700">Delete</button>
          ` : '<span class="text-[10px] text-ink/30 italic">System</span>'}
        </div>
      </div>
    </div>
  `).join('');
}

function filterType(type) {
  activeType = type;
  document.querySelectorAll('[id^=filter-]').forEach(b => {
    b.className = 'pill text-xs bg-line text-ink/70';
  });
  const active = type ? document.getElementById(`filter-${type}`) : document.getElementById('filter-all');
  if (active) active.className = 'pill pill-brand text-xs';
  render();
}

function openModal(template = null) {
  document.getElementById('editingId').value   = template?.id || '';
  document.getElementById('tName').value       = template?.name || '';
  document.getElementById('tType').value       = template?.type || 'caption';
  document.getElementById('tBody').value       = template?.body || '';
  document.getElementById('tDescription').value= template?.description || '';
  document.getElementById('modalTitle').textContent = template ? 'Edit Template' : 'New Template';
  document.getElementById('modal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('modal').classList.add('hidden');
}

function editTemplate(id) {
  const t = templates.find(t => t.id === id);
  if (t) openModal(t);
}

async function deleteTemplate(id) {
  if (!confirm('Delete this template?')) return;
  await fetch(`/api/ai/assistant/templates/${id}`, {
    method:'DELETE',
    headers:{ 'X-Requested-With':'XMLHttpRequest' },
    credentials:'same-origin'
  });
  load();
}

document.getElementById('templateForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const id   = document.getElementById('editingId').value;
  const body = {
    name:        document.getElementById('tName').value,
    type:        document.getElementById('tType').value,
    body:        document.getElementById('tBody').value,
    description: document.getElementById('tDescription').value,
  };
  const url    = id ? `/api/ai/assistant/templates/${id}` : '/api/ai/assistant/templates';
  const method = id ? 'PUT' : 'POST';
  await fetch(url, {
    method, credentials:'same-origin',
    headers:{ 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' },
    body: JSON.stringify(body),
  });
  closeModal();
  load();
});

// Close on backdrop click
document.getElementById('modal').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeModal();
});

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

load();
</script>
@endpush
