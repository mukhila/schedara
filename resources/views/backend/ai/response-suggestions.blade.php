@extends('layouts.backend')
@section('title', 'AI Response Suggestions')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>
  <div>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink mb-1">Response Suggestions</h1>
    <p class="text-ink/60 text-sm mb-6">Generate on-brand replies to comments, DMs, and reviews in seconds.</p>
    <div class="grid lg:grid-cols-[1fr_1fr] gap-5">
      <div class="card p-6">
        <form id="responseForm" class="space-y-4">
          <div><label class="label">Original comment / message *</label>
            <textarea name="comment" rows="4" class="input" placeholder="Paste the comment or message you need to reply to…" required></textarea></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Context</label>
              <select name="context" class="input">
                <option value="comment">Comment</option><option value="dm">Direct message</option>
                <option value="review">Review</option><option value="complaint">Complaint</option>
                <option value="question">Question</option>
              </select></div>
            <div><label class="label">Tone</label>
              <select name="tone" class="input">
                <option value="friendly">Friendly</option><option value="professional">Professional</option>
                <option value="empathetic">Empathetic</option><option value="enthusiastic">Enthusiastic</option>
                <option value="formal">Formal</option>
              </select></div>
          </div>
          <div><label class="label">Your brand / handle</label>
            <input type="text" name="brand" class="input" placeholder="e.g. @NikeRunning"></div>
          <div><label class="label">Key points to address (optional)</label>
            <textarea name="key_points" rows="2" class="input" placeholder="e.g. mention free returns, 24h support…"></textarea></div>
          <div><label class="label">Variations</label>
            <select name="count" class="input">
              <option value="2">2</option><option value="3" selected>3</option><option value="5">5</option>
            </select></div>
          <div><label class="label">AI Provider</label>
            <select name="provider" class="input">
              <option value="">Default</option>
              @foreach($configured as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
            </select></div>
          <button type="submit" class="w-full bg-brand-600 text-white font-bold py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Generate Responses</button>
        </form>
      </div>

      <div class="space-y-4">
        <div id="results" class="hidden space-y-3"></div>
        <div id="emptyState" class="card p-10 flex flex-col items-center justify-center text-center text-ink/40">
          <svg class="w-10 h-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
          <p class="text-sm">Paste a comment and click Generate.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('responseForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target));
  e.submitter.textContent = 'Generating…'; e.submitter.disabled = true;

  const res  = await fetch('/api/ai/assistant/response-suggestions', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  const json = await res.json();
  e.submitter.textContent = 'Generate Responses'; e.submitter.disabled = false;
  if (!res.ok) { alert(json.error); return; }

  const suggestions = json.data.suggestions || [];
  const r = document.getElementById('results');
  r.innerHTML = suggestions.map((s, i) => `
    <div class="card p-5 space-y-2">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-ink/50 uppercase tracking-wider">Option ${i + 1}</span>
        <div class="flex gap-1.5">
          ${s.tone ? `<span class="pill pill-brand text-xs">${escHtml(s.tone)}</span>` : ''}
          ${s.length ? `<span class="pill text-xs bg-line text-ink/70">${escHtml(s.length)}</span>` : ''}
        </div>
      </div>
      <p class="text-sm text-ink leading-relaxed whitespace-pre-wrap">${escHtml(s.text || s)}</p>
      <button onclick="copy(this,'${escAttr(s.text || s)}')" class="text-xs font-bold text-brand-600 hover:text-brand-700">Copy</button>
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
