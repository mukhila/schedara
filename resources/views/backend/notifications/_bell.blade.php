{{--
  Notification bell dropdown.
  Expects: $bellNotifications (Collection), $unread (int)
  Included in layouts/backend.blade.php topbar.
--}}

<div class="relative" id="notif-wrap">

  {{-- Bell trigger --}}
  <button
    class="app-icon-btn"
    id="notif-btn"
    title="Notifications"
    onclick="toggleBell(event)"
    aria-haspopup="true"
    aria-expanded="false"
  >
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 21a2 2 0 0 0 4 0"/>
    </svg>
    @if($unread > 0)
      <span class="dot" id="notif-dot"></span>
    @endif
  </button>

  {{-- Dropdown panel --}}
  <div
    id="notif-panel"
    class="hidden absolute right-0 mt-2 w-80 bg-white border border-line rounded-xl shadow-xl z-50"
    style="top:100%"
  >
    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-line">
      <span class="text-sm font-bold text-ink">Notifications
        @if($unread > 0)
          <span class="ml-1 text-[10px] font-bold text-white px-1.5 py-0.5 rounded-full" style="background:var(--coral)" id="notif-count">{{ $unread }}</span>
        @endif
      </span>
      <div class="flex items-center gap-2">
        @if($unread > 0)
        <button
          onclick="markAllRead(event)"
          class="text-[11px] font-semibold text-brand-600 hover:text-brand-800 transition-colors"
        >Mark all read</button>
        @endif
        <a href="{{ route('notifications.index') }}" class="text-[11px] font-semibold text-ink/40 hover:text-ink">See all</a>
      </div>
    </div>

    {{-- List --}}
    <div class="max-h-72 overflow-y-auto divide-y divide-line" id="notif-list">
      @forelse($bellNotifications as $notif)
        <div
          class="flex gap-3 px-4 py-3 hover:bg-paper transition-colors cursor-pointer {{ $notif->isRead() ? 'opacity-60' : '' }}"
          id="notif-item-{{ $notif->id }}"
          onclick="handleNotifClick({{ $notif->id }}, '{{ $notif->action_url }}')"
        >
          {{-- Category icon --}}
          <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center
            {{ $notif->priority === 'critical' ? 'bg-coral/10' : ($notif->priority === 'high' ? 'bg-gold/15' : 'bg-brand-100') }}">
            @include('backend.notifications._icon', ['icon' => $notif->categoryIcon()])
          </div>

          {{-- Body --}}
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-1">
              <p class="text-xs font-semibold text-ink leading-tight truncate">{{ $notif->title() }}</p>
              @if(!$notif->isRead())
                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 mt-1" style="background:var(--brand)"></span>
              @endif
            </div>
            <p class="text-[11px] text-ink/50 mt-0.5 leading-snug line-clamp-2">{{ $notif->body() }}</p>
            <p class="text-[10px] text-ink/30 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
          </div>
        </div>
      @empty
        <div class="px-4 py-8 text-center">
          <svg class="w-8 h-8 mx-auto mb-2 opacity-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 21a2 2 0 0 0 4 0"/></svg>
          <p class="text-xs text-ink/40">No notifications yet</p>
        </div>
      @endforelse
    </div>

    {{-- Footer --}}
    @if($bellNotifications->isNotEmpty())
    <div class="px-4 py-2 border-t border-line">
      <a href="{{ route('notifications.index') }}" class="block text-center text-xs font-semibold text-brand-600 hover:text-brand-800 py-1 transition-colors">
        View all notifications
      </a>
    </div>
    @endif
  </div>
</div>

{{-- Inline icon partial --}}
@once
<script>
const _bellToken = document.querySelector('meta[name="csrf-token"]')?.content;

function toggleBell(e) {
  e.stopPropagation();
  const panel = document.getElementById('notif-panel');
  const btn   = document.getElementById('notif-btn');
  const open  = !panel.classList.contains('hidden');
  panel.classList.toggle('hidden', open);
  btn.setAttribute('aria-expanded', String(!open));
}

document.addEventListener('click', function(e) {
  const wrap = document.getElementById('notif-wrap');
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById('notif-panel')?.classList.add('hidden');
  }
});

function handleNotifClick(id, url) {
  markRead(id);
  if (url) window.location = url;
}

function markRead(id) {
  const item = document.getElementById('notif-item-' + id);
  if (!item) return;
  item.classList.add('opacity-60');
  item.querySelector('span[style*="--brand"]')?.remove();

  fetch('/api/notifications/' + id + '/read', {
    method: 'PATCH',
    headers: { 'X-CSRF-TOKEN': _bellToken, 'Accept': 'application/json' },
  });

  _decrementCount();
}

function markAllRead(e) {
  e.stopPropagation();
  document.querySelectorAll('#notif-list [id^="notif-item-"]').forEach(el => {
    el.classList.add('opacity-60');
    el.querySelector('span[style*="--brand"]')?.remove();
  });

  fetch('/api/notifications/read-all', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': _bellToken, 'Accept': 'application/json' },
  });

  const badge = document.getElementById('notif-count');
  if (badge) badge.remove();
  document.getElementById('notif-dot')?.remove();
  document.querySelector('[onclick="markAllRead(event)"]')?.remove();
}

function _decrementCount() {
  const badge = document.getElementById('notif-count');
  if (!badge) return;
  const n = parseInt(badge.textContent) - 1;
  if (n <= 0) {
    badge.remove();
    document.getElementById('notif-dot')?.remove();
  } else {
    badge.textContent = n;
  }
}
</script>
@endonce
