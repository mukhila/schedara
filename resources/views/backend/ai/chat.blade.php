@extends('layouts.backend')
@section('title', 'AI Chat')

@section('content')
<div class="grid lg:grid-cols-[220px_1fr] gap-6" style="height:calc(100vh - 120px)">
  <div class="flex flex-col">
    @include('backend.ai._sidebar')

    {{-- Conversation list --}}
    <div class="card p-3 flex-1 overflow-y-auto">
      <div class="flex items-center justify-between mb-2">
        <div class="text-[10px] font-bold uppercase tracking-widest text-ink/40">Conversations</div>
        <button onclick="newConversation()" class="text-xs font-bold text-brand-600 hover:text-brand-700">+ New</button>
      </div>
      <div id="conversationList" class="space-y-1 text-sm"></div>
    </div>
  </div>

  {{-- Chat area --}}
  <div class="card flex flex-col" style="height:calc(100vh - 120px)">
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-3 border-b border-line flex-shrink-0">
      <div id="chatTitle" class="font-bold text-ink">AI Marketing Assistant</div>
      <div class="flex items-center gap-2">
        <select id="providerSelect" class="input text-xs py-1 px-2 h-auto">
          @foreach($configured as $p)
          <option value="{{ $p }}">{{ ucfirst($p) }}</option>
          @endforeach
        </select>
        @if($brandVoices->count() > 0)
        <select id="brandVoiceSelect" class="input text-xs py-1 px-2 h-auto">
          <option value="">No brand voice</option>
          @foreach($brandVoices as $v)
          <option value="{{ $v->id }}">{{ $v->name }}</option>
          @endforeach
        </select>
        @endif
      </div>
    </div>

    {{-- Messages --}}
    <div id="chatMessages" class="flex-1 overflow-y-auto p-5 space-y-4">
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 3.9 2.4-7.4L2 9.4h7.6z"/></svg>
        </div>
        <div class="bg-paper rounded-xl px-4 py-3 max-w-lg text-sm text-ink">
          Hi! I'm your AI marketing assistant. I can help you write captions, plan campaigns, optimize content, analyze trends, and more. What would you like to work on today?
        </div>
      </div>
    </div>

    {{-- Input --}}
    <div class="border-t border-line p-4 flex-shrink-0">
      <div class="flex items-end gap-3">
        <textarea id="messageInput" rows="2"
          placeholder="Ask anything about social media, content, marketing strategy…"
          class="input flex-1 resize-none"></textarea>
        <button id="sendBtn" onclick="sendMessage()"
          class="bg-brand-600 text-white font-bold px-4 py-2.5 rounded-lg hover:bg-brand-700 transition-colors flex items-center gap-1.5 flex-shrink-0">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
          Send
        </button>
      </div>
      <div class="text-[10px] text-ink/40 mt-1.5">Press Enter to send · Shift+Enter for new line</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let currentConvoUuid = null;

// ── New conversation ──────────────────────────────────────────
async function newConversation() {
  const provider = document.getElementById('providerSelect')?.value || 'openai';

  const res  = await fetch('/api/ai/assistant/conversations', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
    body: JSON.stringify({ provider }),
  });
  const json = await res.json();
  currentConvoUuid = json.data.uuid;
  clearMessages();
  loadConversations();
}

// ── Load conversations list ───────────────────────────────────
async function loadConversations() {
  const res  = await fetch('/api/ai/assistant/conversations', { credentials: 'same-origin' });
  const json = await res.json();
  const list = document.getElementById('conversationList');
  list.innerHTML = (json.data || []).map(c => `
    <button onclick="loadConversation('${c.uuid}')"
      class="w-full text-left px-2.5 py-2 rounded-lg text-xs font-semibold transition-colors truncate
        ${currentConvoUuid === c.uuid ? 'bg-brand-50 text-brand-700' : 'text-ink/70 hover:bg-paper'}">
      ${escHtml(c.title || 'New conversation')}
    </button>
  `).join('');
}

// ── Load a specific conversation ──────────────────────────────
async function loadConversation(uuid) {
  currentConvoUuid = uuid;
  const res  = await fetch(`/api/ai/assistant/conversations/${uuid}`, { credentials: 'same-origin' });
  const json = await res.json();
  clearMessages();
  (json.data.messages || []).forEach(m => appendMessage(m.role, m.content));
  document.getElementById('chatTitle').textContent = json.data.title || 'Conversation';
  loadConversations();
}

// ── Send message ──────────────────────────────────────────────
async function sendMessage() {
  const input   = document.getElementById('messageInput');
  const message = input.value.trim();
  if (!message) return;

  // Auto-create conversation if needed
  if (!currentConvoUuid) await newConversation();

  input.value = '';
  appendMessage('user', message);
  const typingId = appendTyping();

  const brandVoiceId = document.getElementById('brandVoiceSelect')?.value || null;

  try {
    const res  = await fetch(`/api/ai/assistant/conversations/${currentConvoUuid}/messages`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      body: JSON.stringify({ message, brand_voice_id: brandVoiceId }),
    });
    const json = await res.json();

    removeTyping(typingId);

    if (!res.ok) { appendMessage('assistant', `Error: ${json.error || 'Failed to get response.'}`); return; }

    appendMessage('assistant', json.data.content);
    loadConversations();
  } catch {
    removeTyping(typingId);
    appendMessage('assistant', 'Connection error. Please try again.');
  }
}

function appendMessage(role, content) {
  const wrap = document.getElementById('chatMessages');
  const isUser = role === 'user';
  const el = document.createElement('div');
  el.className = `flex items-start gap-3 ${isUser ? 'flex-row-reverse' : ''}`;
  el.innerHTML = `
    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold
      ${isUser ? 'bg-ink text-white' : 'bg-brand-100 text-brand-700'}">
      ${isUser ? 'U' : 'AI'}
    </div>
    <div class="rounded-xl px-4 py-3 max-w-xl text-sm whitespace-pre-wrap leading-relaxed
      ${isUser ? 'bg-brand-600 text-white' : 'bg-paper text-ink'}">
      ${escHtml(content)}
    </div>
  `;
  wrap.appendChild(el);
  wrap.scrollTop = wrap.scrollHeight;
}

function appendTyping() {
  const id   = 'typing-' + Date.now();
  const wrap = document.getElementById('chatMessages');
  const el   = document.createElement('div');
  el.id      = id;
  el.className = 'flex items-start gap-3';
  el.innerHTML = `
    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0 text-xs font-bold text-brand-700">AI</div>
    <div class="bg-paper rounded-xl px-4 py-3 text-sm text-ink/40 italic">Thinking…</div>
  `;
  wrap.appendChild(el);
  wrap.scrollTop = wrap.scrollHeight;
  return id;
}

function removeTyping(id) {
  document.getElementById(id)?.remove();
}

function clearMessages() {
  document.getElementById('chatMessages').innerHTML = '';
}

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// Send on Enter, new line on Shift+Enter
document.getElementById('messageInput').addEventListener('keydown', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

// Load on init
loadConversations();
</script>
@endpush
