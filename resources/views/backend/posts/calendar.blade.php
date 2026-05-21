@extends('layouts.backend')

@section('title', 'Content Calendar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-ink">Content Calendar</h1>
      <p class="text-sm text-ink/50 mt-0.5">Visualize and manage your scheduled posts</p>
    </div>
    <a href="{{ route('posts.create') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold text-white flex items-center gap-2 transition-opacity hover:opacity-90" style="background:#4a8ccc">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Post
    </a>
  </div>

  {{-- FullCalendar container --}}
  <div class="bg-white rounded-2xl border border-line p-4 min-h-[600px]">
    <div id="calendar"></div>
  </div>

  {{-- Post detail slide-over --}}
  <div id="post-panel" class="hidden fixed right-0 top-0 bottom-0 w-96 bg-white shadow-2xl border-l border-line z-50 flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-line">
      <h3 class="font-semibold text-ink" id="panel-title">Post Details</h3>
      <button onclick="closePanel()" class="text-ink/30 hover:text-ink">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="flex-1 overflow-y-auto p-4" id="panel-body">
      <p class="text-sm text-ink/50">Loading…</p>
    </div>
    <div class="p-4 border-t border-line flex gap-2">
      <a id="panel-edit-link" href="#" class="flex-1 py-2 text-sm font-medium text-center rounded-lg border border-line hover:bg-line/40 transition-colors">Edit</a>
      <a id="panel-view-link" href="#" class="flex-1 py-2 text-sm font-semibold text-center rounded-lg text-white transition-opacity hover:opacity-90" style="background:#4a8ccc">View</a>
    </div>
  </div>
  <div id="panel-overlay" class="hidden fixed inset-0 bg-ink/20 z-40" onclick="closePanel()"></div>
</div>

{{-- FullCalendar --}}
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

<script>
const CALENDAR_EVENTS_URL = '/api/calendar/events';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,listWeek',
    },
    height: 'auto',
    events: function(info, successCallback, failureCallback) {
      fetch(`/api/calendar/events?start=${info.startStr}&end=${info.endStr}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      })
      .then(r => r.json())
      .then(data => successCallback(data))
      .catch(() => failureCallback());
    },
    eventClick: function(info) {
      openPanel(info.event);
    },
    eventContent: function(arg) {
      return {
        html: `<div class="px-1.5 py-0.5 text-xs font-medium truncate">${arg.event.title}</div>`
      };
    },
  });

  calendar.render();
});

function openPanel(event) {
  const props = event.extendedProps;
  document.getElementById('panel-title').textContent = event.title;
  document.getElementById('panel-body').innerHTML = `
    <div class="space-y-4">
      <div>
        <p class="text-xs text-ink/40 mb-1">Status</p>
        <span class="text-sm font-medium capitalize">${props.status || '—'}</span>
      </div>
      <div>
        <p class="text-xs text-ink/40 mb-1">Scheduled</p>
        <p class="text-sm">${event.start?.toLocaleString() || '—'}</p>
      </div>
      <div>
        <p class="text-xs text-ink/40 mb-1">Platforms</p>
        <div class="flex flex-wrap gap-1 mt-1">
          ${(props.platforms || []).map(p => `<span class="text-xs px-2 py-0.5 rounded-full bg-line/60 text-ink/60">${p}</span>`).join('')}
        </div>
      </div>
    </div>
  `;

  if (props.post_id) {
    document.getElementById('panel-edit-link').href = `/posts/${props.post_id}/edit`;
    document.getElementById('panel-view-link').href = `/posts/${props.post_id}`;
  }

  document.getElementById('post-panel').classList.remove('hidden');
  document.getElementById('panel-overlay').classList.remove('hidden');
}

function closePanel() {
  document.getElementById('post-panel').classList.add('hidden');
  document.getElementById('panel-overlay').classList.add('hidden');
}
</script>
@endsection
