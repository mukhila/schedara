{{--
  Dashboard layout toolbar.
  Expects: $layout (array with 'order', 'hidden'), $dateRange, $filter
--}}

<div class="flex items-center justify-between gap-3 flex-wrap mb-6">

  {{-- Left: title + date info --}}
  <div>
    <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Insights · All channels</div>
    <h1 class="text-3xl font-extrabold tracking-tight text-ink">Analytics</h1>
    <p class="text-ink/60 mt-1 text-sm">
      Welcome back, <span class="font-semibold text-ink">{{ auth()->user()->name }}</span> —
      here's what's working across your social presence.
    </p>
  </div>

  {{-- Right: date range + actions --}}
  <div class="flex items-center gap-2 flex-wrap">
    {{-- Date range presets --}}
    <form method="GET" action="{{ route('dashboard') }}" class="inline-flex items-center bg-white border border-line rounded-lg p-1 gap-0.5">
      @php $days = $dateRange['days'] ?? 30; @endphp
      @foreach([7 => '7d', 30 => '30d', 90 => '90d'] as $d => $label)
      <button type="submit" name="date_from" value="{{ now()->subDays($d - 1)->toDateString() }}"
        class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors
          {{ $days == $d ? 'bg-ink text-white' : 'text-ink/50 hover:text-ink' }}">
        {{ $label }}
        @if($days == $d)<input type="hidden" name="date_to" value="{{ now()->toDateString() }}">@endif
      </button>
      @endforeach
    </form>

    {{-- Export buttons --}}
    <div class="relative" id="export-wrap">
      <button onclick="toggleExport(event)"
        class="bg-white border border-line text-ink font-semibold text-sm px-3 py-2 rounded-lg hover:border-brand-300 transition-colors flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        Export
        <svg class="w-3 h-3 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div id="export-panel" class="hidden absolute right-0 mt-1.5 w-44 bg-white border border-line rounded-xl shadow-lg z-30 py-1">
        <a href="{{ route('dashboard.export.pdf', request()->only('date_from','date_to')) }}"
           class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-ink hover:bg-paper transition-colors">
          <svg class="w-4 h-4 text-coral opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          PDF / Print
        </a>
        <a href="{{ route('dashboard.export.excel', request()->only('date_from','date_to')) }}"
           class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-ink hover:bg-paper transition-colors">
          <svg class="w-4 h-4 text-mint opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
          Excel / CSV
        </a>
      </div>
    </div>

    {{-- Layout edit toggle --}}
    <button id="edit-layout-btn" onclick="toggleEditMode()"
      class="bg-white border border-line text-ink font-semibold text-sm px-3 py-2 rounded-lg hover:border-brand-300 transition-colors flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
      <span id="edit-label">Edit layout</span>
    </button>

    {{-- Save layout (shown in edit mode) --}}
    <button id="save-layout-btn" onclick="saveLayout()"
      class="hidden bg-ink text-white font-semibold text-sm px-4 py-2 rounded-lg hover:bg-brand-800 transition-colors">
      Save layout
    </button>

    {{-- Reset layout (shown in edit mode) --}}
    <button id="reset-layout-btn" onclick="resetLayout()"
      class="hidden bg-white border border-line text-ink/50 font-semibold text-sm px-3 py-2 rounded-lg hover:text-coral hover:border-coral/30 transition-colors">
      Reset
    </button>

    <a href="{{ route('analytics.index') }}" class="bg-brand-600 text-white font-semibold text-sm px-3 py-2 rounded-lg hover:bg-brand-700 transition-colors flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Full Analytics
    </a>
  </div>

</div>

{{-- Edit mode banner --}}
<div id="edit-banner" class="hidden mb-4 bg-brand-50 border border-brand-200 rounded-xl px-4 py-3 flex items-center gap-3">
  <svg class="w-4 h-4 text-brand-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
  <p class="text-sm text-brand-700 font-medium">
    <strong>Layout editing:</strong> drag widgets to reorder · click <span class="font-bold">✕</span> to hide · click <strong>Save layout</strong> when done.
  </p>
</div>

{{-- Hidden widgets tray (shown in edit mode) --}}
<div id="hidden-tray" class="hidden mb-4">
  <p class="text-xs font-bold text-ink/40 uppercase tracking-widest mb-2">Hidden widgets — click to restore</p>
  <div id="hidden-tray-items" class="flex flex-wrap gap-2"></div>
</div>
