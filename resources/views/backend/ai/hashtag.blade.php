@extends('layouts.backend')
@section('title', 'AI Hashtag Generator')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Hashtag Generator</h1>
    <p class="text-ink/60 text-sm mb-6">Get trending, niche, and industry hashtags powered by AI.</p>
    <div class="grid lg:grid-cols-[1fr_1.4fr] gap-5">
      <div class="card p-6">
        <form id="hashtagForm" class="space-y-4">
          <div>
            <label class="label">Topic / Post description *</label>
            <textarea name="topic" rows="3" class="input" placeholder="Describe your post content…" required></textarea>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Platform</label>
              <select name="platform" class="input">
                <option value="instagram">Instagram</option>
                <option value="tiktok">TikTok</option>
                <option value="twitter">Twitter/X</option>
                <option value="linkedin">LinkedIn</option>
                <option value="youtube">YouTube</option>
              </select>
            </div>
            <div>
              <label class="label">Count</label>
              <select name="count" class="input">
                <option value="15">15</option>
                <option value="20">20</option>
                <option value="30" selected>30</option>
              </select>
            </div>
          </div>
          <div>
            <label class="label">Industry / Niche</label>
            <input type="text" name="industry" placeholder="e.g. fitness, fashion, tech" class="input">
          </div>
          <div>
            <label class="label">AI Provider</label>
            <select name="provider" class="input">
              <option value="">Default</option>
              @foreach($configured as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
            </select>
          </div>
          <button type="submit" class="w-full bg-brand-600 text-white font-bold py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Generate Hashtags</button>
        </form>
      </div>

      <div id="results" class="hidden space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-bold">Generated Hashtags</h3>
          <button id="copyAllBtn" class="pill pill-brand text-xs cursor-pointer">Copy all</button>
        </div>
        <div id="hashtagGrid" class="card p-5 flex flex-wrap gap-2"></div>
        <div id="hashtagTable" class="card overflow-hidden">
          <table class="w-full text-sm">
            <thead><tr class="text-[10px] font-bold uppercase text-ink/40 border-b border-line">
              <th class="px-4 py-2 text-left">Hashtag</th>
              <th class="px-4 py-2">Category</th>
              <th class="px-4 py-2">Reach</th>
              <th class="px-4 py-2">Competition</th>
            </tr></thead>
            <tbody id="hashtagTbody" class="divide-y divide-line"></tbody>
          </table>
        </div>
      </div>
      <div id="emptyState" class="card p-10 flex flex-col items-center justify-center text-center text-ink/40">
        <svg class="w-10 h-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
        <p class="text-sm">Enter a topic and click Generate.</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const pillColors = { trending:'pill-coral', niche:'pill-mint', branded:'pill-brand', industry:'pill-gold', community:'bg-line text-ink/70' };
let allTags = [];

document.getElementById('hashtagForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  e.submitter.textContent = 'Generating…'; e.submitter.disabled = true;

  const res  = await fetch('/api/ai/assistant/hashtags', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  const json = await res.json();
  e.submitter.textContent = 'Generate Hashtags'; e.submitter.disabled = false;
  if (!res.ok) { alert(json.error); return; }

  allTags = json.data.hashtags || [];

  document.getElementById('hashtagGrid').innerHTML = allTags.map(h =>
    `<button onclick="copy(this, '${h.tag}')" class="pill ${pillColors[h.category] || ''} text-sm cursor-pointer">${h.tag}</button>`
  ).join('');

  document.getElementById('hashtagTbody').innerHTML = allTags.map(h => `
    <tr class="hover:bg-paper/80">
      <td class="px-4 py-2 font-bold text-brand-600">${h.tag}</td>
      <td class="px-4 py-2 text-center capitalize text-ink/70">${h.category}</td>
      <td class="px-4 py-2 text-center"><span class="pill text-xs ${h.estimated_reach==='high'?'pill-mint':h.estimated_reach==='medium'?'pill-gold':'pill-coral'}">${h.estimated_reach}</span></td>
      <td class="px-4 py-2 text-center"><span class="pill text-xs">${h.competition}</span></td>
    </tr>
  `).join('');

  document.getElementById('emptyState').classList.add('hidden');
  document.getElementById('results').classList.remove('hidden');
});

document.getElementById('copyAllBtn').addEventListener('click', () => {
  navigator.clipboard.writeText(allTags.map(h => h.tag).join(' ')).then(() => {
    document.getElementById('copyAllBtn').textContent = 'Copied!';
    setTimeout(() => document.getElementById('copyAllBtn').textContent = 'Copy all', 1500);
  });
});

function copy(btn, text) {
  navigator.clipboard.writeText(text).then(() => { btn.classList.add('ring-2','ring-brand-400'); setTimeout(() => btn.classList.remove('ring-2','ring-brand-400'), 1000); });
}
</script>
@endpush
