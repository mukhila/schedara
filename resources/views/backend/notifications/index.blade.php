@extends('layouts.backend')
@section('title', 'Notifications')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
  <div>
    <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Inbox · All channels</div>
    <h1 class="text-3xl font-extrabold tracking-tight text-ink">Notifications</h1>
    <p class="text-ink/60 mt-1 text-sm">
      {{ $unreadCount > 0 ? $unreadCount . ' unread' : 'All caught up' }} —
      updates from posts, media, analytics, and your team.
    </p>
  </div>

  <div class="flex items-center gap-2">
    @if($unreadCount > 0)
    <button
      onclick="markAllRead()"
      class="bg-white border border-line text-ink text-sm font-semibold px-3 py-2 rounded-lg hover:border-brand-300 transition-colors"
    >Mark all read</button>
    @endif
    <a href="{{ route('notifications.preferences') }}"
       class="bg-white border border-line text-ink text-sm font-semibold px-3 py-2 rounded-lg hover:border-brand-300 transition-colors flex items-center gap-1.5">
      <svg class="w-4 h-4 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 0 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 0 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 0 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 0 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
      Preferences
    </a>
  </div>
</div>

{{-- ── Filter tabs ─────────────────────────────────────────────── --}}
<div class="flex items-center gap-1 mb-4 flex-wrap">
  @php
    $tabs = [
      null       => 'All',
      'post'     => 'Posts',
      'media'    => 'Media',
      'analytics'=> 'Analytics',
      'social'   => 'Social',
      'team'     => 'Team',
      'system'   => 'System',
    ];
    $filters = [null => 'All', 'unread' => 'Unread', 'read' => 'Read'];
  @endphp

  {{-- Category filter --}}
  @foreach($tabs as $cat => $label)
    <a href="{{ route('notifications.index', array_filter(['category' => $cat, 'filter' => $filter])) }}"
       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors
         {{ $category === $cat ? 'bg-ink text-white' : 'bg-white border border-line text-ink/60 hover:text-ink hover:border-ink/20' }}">
      {{ $label }}
    </a>
  @endforeach

  <div class="flex-1"></div>

  {{-- Read/unread filter --}}
  @foreach($filters as $f => $label)
    <a href="{{ route('notifications.index', array_filter(['category' => $category, 'filter' => $f])) }}"
       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors
         {{ $filter === $f ? 'bg-brand-600 text-white' : 'bg-white border border-line text-ink/60 hover:text-ink' }}">
      {{ $label }}
    </a>
  @endforeach
</div>

{{-- ── Notification list ───────────────────────────────────────── --}}
<div class="card divide-y divide-line" id="notif-list">
  @forelse($notifications as $notif)
    <div
      class="flex gap-4 px-5 py-4 hover:bg-paper/60 transition-colors {{ $notif->isRead() ? '' : 'bg-brand-50/40' }}"
      id="nrow-{{ $notif->id }}"
    >
      {{-- Icon --}}
      <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center
        {{ $notif->priority === 'critical' ? 'bg-coral/10' : ($notif->priority === 'high' ? 'bg-gold/15' : 'bg-brand-100') }}">
        @include('backend.notifications._icon', ['icon' => $notif->categoryIcon()])
      </div>

      {{-- Content --}}
      <div class="flex-1 min-w-0">
        <div class="flex items-start gap-2">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-sm font-bold text-ink">{{ $notif->title() }}</span>
              @if(!$notif->isRead())
                <span class="pill pill-brand pill-dot text-[10px]">Unread</span>
              @endif
              @if($notif->priority === 'high')
                <span class="pill pill-gold text-[10px]">High</span>
              @elseif($notif->priority === 'critical')
                <span class="pill pill-coral text-[10px]">Critical</span>
              @endif
            </div>
            <p class="text-sm text-ink/60 mt-0.5 leading-relaxed">{{ $notif->body() }}</p>
            <p class="text-xs text-ink/30 mt-1">{{ $notif->created_at->format('M j, Y g:ia') }} · {{ $notif->created_at->diffForHumans() }}</p>
          </div>

          {{-- Actions --}}
          <div class="flex items-center gap-1.5 flex-shrink-0">
            @if($notif->action_url)
            <a href="{{ $notif->action_url }}"
               onclick="markRead({{ $notif->id }})"
               class="text-xs font-semibold text-brand-600 hover:text-brand-800 px-2.5 py-1 bg-brand-50 rounded-lg transition-colors whitespace-nowrap">
              View
            </a>
            @endif
            @if(!$notif->isRead())
            <button
              onclick="markRead({{ $notif->id }})"
              class="text-[11px] font-semibold text-ink/40 hover:text-ink px-2 py-1 rounded-lg hover:bg-paper transition-colors whitespace-nowrap"
              title="Mark as read"
            >
              ✓ Read
            </button>
            @endif
            <button
              onclick="deleteNotif({{ $notif->id }})"
              class="text-ink/20 hover:text-coral p-1.5 rounded-lg hover:bg-coral/5 transition-colors"
              title="Delete"
            >
              <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="px-6 py-16 text-center">
      <svg class="w-10 h-10 mx-auto mb-3 opacity-15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 21a2 2 0 0 0 4 0"/></svg>
      <p class="text-sm font-semibold text-ink/30">No notifications here</p>
      <p class="text-xs text-ink/20 mt-1">
        {{ $filter === 'unread' ? 'All caught up — nothing unread.' : 'Notifications from posts, media, analytics, and your team will appear here.' }}
      </p>
    </div>
  @endforelse
</div>

{{-- Pagination --}}
@if($notifications->hasPages())
<div class="mt-4">{{ $notifications->appends(request()->query())->links() }}</div>
@endif

@endsection

@push('scripts')
<script>
const _csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function markRead(id) {
  const row = document.getElementById('nrow-' + id);
  if (row) {
    row.classList.remove('bg-brand-50/40');
    row.querySelector('.pill-brand')?.remove();
    row.querySelector('[onclick*="markRead"]')?.remove();
  }
  fetch('/api/notifications/' + id + '/read', {
    method: 'PATCH',
    headers: { 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json' },
  });
}

function markAllRead() {
  document.querySelectorAll('[id^="nrow-"]').forEach(row => {
    row.classList.remove('bg-brand-50/40');
    row.querySelector('.pill-brand')?.remove();
    row.querySelectorAll('[onclick*="markRead"]').forEach(el => el.remove());
  });
  document.querySelector('[onclick="markAllRead()"]')?.remove();
  fetch('/api/notifications/read-all', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json' },
  });
}

function deleteNotif(id) {
  const row = document.getElementById('nrow-' + id);
  if (row) {
    row.style.transition = 'opacity .2s';
    row.style.opacity = '0';
    setTimeout(() => row.remove(), 200);
  }
  fetch('/api/notifications/' + id, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json' },
  });
}
</script>
@endpush
