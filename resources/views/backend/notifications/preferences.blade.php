@extends('layouts.backend')
@section('title', 'Notification Preferences')

@section('content')

<div class="max-w-4xl">

  {{-- ── Header ──────────────────────────────────────────────── --}}
  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('notifications.index') }}"
       class="w-8 h-8 flex items-center justify-center rounded-lg border bg-white text-ink/40 hover:text-ink transition-colors" style="border-color:var(--line)">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div>
      <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-1">Notifications · Preferences</div>
      <h1 class="text-2xl font-extrabold tracking-tight text-ink">Notification Settings</h1>
    </div>
  </div>

  {{-- ── Contact numbers (for WhatsApp & SMS) ──────────────── --}}
  <div class="card px-5 py-4 mb-5" x-data="contactForm()" x-init="init()">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-3">Contact Numbers</div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="text-xs font-semibold text-ink/50 block mb-1">Phone (SMS)</label>
        <input x-model="phone" type="tel" placeholder="+1 555 000 0000"
               class="w-full px-3 py-2 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-brand-400/30"
               style="border-color:var(--line)" @change="save">
      </div>
      <div>
        <label class="text-xs font-semibold text-ink/50 block mb-1">WhatsApp Number</label>
        <input x-model="whatsapp" type="tel" placeholder="+1 555 000 0000"
               class="w-full px-3 py-2 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-brand-400/30"
               style="border-color:var(--line)" @change="save">
      </div>
    </div>
    <p class="text-xs text-ink/40 mt-2">Required to receive WhatsApp and SMS notifications. Include country code.</p>
    <div x-show="saved" x-transition class="text-xs text-mint font-semibold mt-1">Saved!</div>
  </div>

  {{-- ── Quick channel links ──────────────────────────────── --}}
  <div class="flex gap-3 mb-5 flex-wrap">
    <a href="{{ route('notifications.slack') }}"
       class="flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-lg border hover:bg-paper transition-colors" style="border-color:var(--line)">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/></svg>
      Slack Integration
    </a>
    <a href="{{ route('notifications.templates') }}"
       class="flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-lg border hover:bg-paper transition-colors" style="border-color:var(--line)">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      Manage Templates
    </a>
  </div>

  {{-- ── Preferences grid ────────────────────────────────── --}}
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <form id="pref-form">
        {{-- Column headers --}}
        @php $channelLabels = config('notifications.channel_labels', []); @endphp
        <div class="flex items-center gap-0 px-5 py-3 border-b text-[10px] font-bold uppercase tracking-[1.5px] text-ink/30 min-w-[700px]" style="border-color:var(--line)">
          <div class="flex-1">Category</div>
          @foreach(config('notifications.channels', []) as $chan)
          <div class="w-16 text-center">{{ $channelLabels[$chan] ?? ucfirst($chan) }}</div>
          @endforeach
        </div>

        @php $allChannels = config('notifications.channels', []); @endphp
        @foreach($categories as $catKey => $cat)
        <div class="flex items-center gap-0 px-5 py-4 border-b last:border-b-0 min-w-[700px]" style="border-color:var(--line)">
          <div class="flex items-center gap-3 flex-1">
            <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center flex-shrink-0">
              @include('backend.notifications._icon', ['icon' => match($cat['icon']) {
                'edit'        => 'edit',
                'image'       => 'image',
                'bar-chart'   => 'bar-chart-2',
                'share-2'     => 'share-2',
                'users'       => 'users',
                'credit-card' => 'credit-card',
                default       => 'bell',
              }])
            </div>
            <div>
              <div class="text-sm font-bold text-ink">{{ $cat['label'] }}</div>
              <div class="text-xs text-ink/40">{{ ucfirst($catKey) }} events</div>
            </div>
          </div>

          @foreach($allChannels as $chan)
          <div class="w-16 flex justify-center">
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                class="sr-only peer"
                data-category="{{ $catKey }}"
                data-channel="{{ $chan }}"
                {{ ($preferences[$catKey][$chan] ?? config("notifications.defaults.{$chan}", false)) ? 'checked' : '' }}
                onchange="savePref('{{ $catKey }}', '{{ $chan }}', this.checked)"
              >
              <div class="w-9 h-5 bg-line peer-focus:outline-none rounded-full peer
                peer-checked:after:translate-x-4 after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all
                peer-checked:bg-brand-500"></div>
            </label>
          </div>
          @endforeach
        </div>
        @endforeach
      </form>
    </div>
  </div>

  {{-- Save confirmation toast --}}
  <div id="save-toast"
       class="fixed bottom-6 right-6 bg-ink text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-xl opacity-0 transition-opacity duration-300 pointer-events-none z-50">
    ✓ Preferences saved
  </div>

</div>
@endsection

@section('scripts')
<script>
const _csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let _saveTimer;

function savePref(category, channel, enabled) {
  clearTimeout(_saveTimer);
  _saveTimer = setTimeout(() => {
    const prefs = {};
    document.querySelectorAll('#pref-form input[type=checkbox]').forEach(cb => {
      const cat  = cb.dataset.category;
      const chan = cb.dataset.channel;
      if (!prefs[cat]) prefs[cat] = {};
      prefs[cat][chan] = cb.checked ? 1 : 0;
    });

    fetch('/api/notifications/preferences', {
      method:  'PUT',
      headers: { 'X-CSRF-TOKEN': _csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body:    JSON.stringify({ preferences: prefs }),
    }).then(() => showToast());
  }, 400);
}

function showToast() {
  const t = document.getElementById('save-toast');
  t.style.opacity = '1';
  setTimeout(() => { t.style.opacity = '0'; }, 2000);
}

function contactForm() {
  return {
    phone:    '',
    whatsapp: '',
    saved:    false,
    async init() {
      const res  = await fetch('/api/notifications/contacts', { headers: { Accept: 'application/json' } });
      const data = await res.json();
      this.phone    = data.phone_number    ?? '';
      this.whatsapp = data.whatsapp_number ?? '';
    },
    async save() {
      await fetch('/api/notifications/contacts', {
        method:  'PUT',
        headers: { 'X-CSRF-TOKEN': _csrfToken, 'Content-Type': 'application/json', Accept: 'application/json' },
        body:    JSON.stringify({ phone_number: this.phone, whatsapp_number: this.whatsapp }),
      });
      this.saved = true;
      setTimeout(() => { this.saved = false; }, 2000);
    },
  };
}
</script>
@endsection
