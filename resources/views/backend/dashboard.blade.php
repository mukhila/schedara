@extends('layouts.backend')
@section('title', 'Dashboard')

@section('content')

{{-- ── Toolbar: date range, export, layout edit ─────────────────────── --}}
@include('backend.dashboard._layout_toolbar')

{{-- ── Widget Grid ────────────────────────────────────────────────────── --}}
<div id="widget-grid" class="space-y-4">

  {{-- kpi-cards --}}
  <div class="widget-wrap" data-widget="kpi-cards">
    <div class="widget-controls hidden items-center justify-between mb-2 px-1 py-1 rounded-lg border border-brand-200 bg-brand-50/60">
      <div class="widget-drag-handle cursor-grab flex items-center gap-2 text-ink/50 hover:text-ink/80 select-none px-2">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
          <path d="M7 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/>
        </svg>
        <span class="text-xs font-bold">KPI Cards</span>
      </div>
      <button onclick="hideWidget('kpi-cards')" class="flex items-center gap-1 text-xs font-semibold text-ink/50 hover:text-coral px-2 py-1 rounded transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4l12 12M16 4L4 16"/></svg>
        Hide
      </button>
    </div>
    @include('backend.dashboard._widget_kpi')
  </div>

  {{-- engagement --}}
  <div class="widget-wrap" data-widget="engagement">
    <div class="widget-controls hidden items-center justify-between mb-2 px-1 py-1 rounded-lg border border-brand-200 bg-brand-50/60">
      <div class="widget-drag-handle cursor-grab flex items-center gap-2 text-ink/50 hover:text-ink/80 select-none px-2">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/></svg>
        <span class="text-xs font-bold">Engagement Chart</span>
      </div>
      <button onclick="hideWidget('engagement')" class="flex items-center gap-1 text-xs font-semibold text-ink/50 hover:text-coral px-2 py-1 rounded transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4l12 12M16 4L4 16"/></svg>
        Hide
      </button>
    </div>
    @include('backend.dashboard._widget_engagement')
  </div>

  {{-- followers --}}
  <div class="widget-wrap" data-widget="followers">
    <div class="widget-controls hidden items-center justify-between mb-2 px-1 py-1 rounded-lg border border-brand-200 bg-brand-50/60">
      <div class="widget-drag-handle cursor-grab flex items-center gap-2 text-ink/50 hover:text-ink/80 select-none px-2">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/></svg>
        <span class="text-xs font-bold">Follower Growth</span>
      </div>
      <button onclick="hideWidget('followers')" class="flex items-center gap-1 text-xs font-semibold text-ink/50 hover:text-coral px-2 py-1 rounded transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4l12 12M16 4L4 16"/></svg>
        Hide
      </button>
    </div>
    @include('backend.dashboard._widget_followers')
  </div>

  {{-- post-performance --}}
  <div class="widget-wrap" data-widget="post-performance">
    <div class="widget-controls hidden items-center justify-between mb-2 px-1 py-1 rounded-lg border border-brand-200 bg-brand-50/60">
      <div class="widget-drag-handle cursor-grab flex items-center gap-2 text-ink/50 hover:text-ink/80 select-none px-2">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/></svg>
        <span class="text-xs font-bold">Top Posts</span>
      </div>
      <button onclick="hideWidget('post-performance')" class="flex items-center gap-1 text-xs font-semibold text-ink/50 hover:text-coral px-2 py-1 rounded transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4l12 12M16 4L4 16"/></svg>
        Hide
      </button>
    </div>
    @include('backend.dashboard._widget_post_performance')
  </div>

  {{-- platform-comparison --}}
  <div class="widget-wrap" data-widget="platform-comparison">
    <div class="widget-controls hidden items-center justify-between mb-2 px-1 py-1 rounded-lg border border-brand-200 bg-brand-50/60">
      <div class="widget-drag-handle cursor-grab flex items-center gap-2 text-ink/50 hover:text-ink/80 select-none px-2">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/></svg>
        <span class="text-xs font-bold">Platform Comparison</span>
      </div>
      <button onclick="hideWidget('platform-comparison')" class="flex items-center gap-1 text-xs font-semibold text-ink/50 hover:text-coral px-2 py-1 rounded transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4l12 12M16 4L4 16"/></svg>
        Hide
      </button>
    </div>
    @include('backend.dashboard._widget_platform_comparison')
  </div>

  {{-- revenue --}}
  <div class="widget-wrap" data-widget="revenue">
    <div class="widget-controls hidden items-center justify-between mb-2 px-1 py-1 rounded-lg border border-brand-200 bg-brand-50/60">
      <div class="widget-drag-handle cursor-grab flex items-center gap-2 text-ink/50 hover:text-ink/80 select-none px-2">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/></svg>
        <span class="text-xs font-bold">Revenue &amp; ROI</span>
      </div>
      <button onclick="hideWidget('revenue')" class="flex items-center gap-1 text-xs font-semibold text-ink/50 hover:text-coral px-2 py-1 rounded transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4l12 12M16 4L4 16"/></svg>
        Hide
      </button>
    </div>
    @include('backend.dashboard._widget_revenue')
  </div>

  {{-- ai-insights --}}
  <div class="widget-wrap" data-widget="ai-insights">
    <div class="widget-controls hidden items-center justify-between mb-2 px-1 py-1 rounded-lg border border-brand-200 bg-brand-50/60">
      <div class="widget-drag-handle cursor-grab flex items-center gap-2 text-ink/50 hover:text-ink/80 select-none px-2">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM7 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/></svg>
        <span class="text-xs font-bold">AI Insights</span>
      </div>
      <button onclick="hideWidget('ai-insights')" class="flex items-center gap-1 text-xs font-semibold text-ink/50 hover:text-coral px-2 py-1 rounded transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4l12 12M16 4L4 16"/></svg>
        Hide
      </button>
    </div>
    @include('backend.dashboard._widget_ai_insights')
  </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Bootstrap data from PHP ───────────────────────────────────────────
const LAYOUT      = @json($layout);
const TIME_SERIES = @json($timeSeries);
const BY_PLATFORM = @json($byPlatform);

const WIDGET_LABELS = {
  'kpi-cards':           'KPI Cards',
  'engagement':          'Engagement Chart',
  'followers':           'Follower Growth',
  'post-performance':    'Top Posts',
  'platform-comparison': 'Platform Comparison',
  'revenue':             'Revenue & ROI',
  'ai-insights':         'AI Insights',
};

// ── Apply saved layout on page load ──────────────────────────────────
(function applyLayout() {
  const grid   = document.getElementById('widget-grid');
  const order  = LAYOUT.order  || [];
  const hidden = LAYOUT.hidden || [];

  // Reorder widgets according to saved order
  order.forEach(key => {
    const el = grid.querySelector('[data-widget="' + key + '"]');
    if (el) grid.appendChild(el);
  });

  // Hide widgets that were hidden
  hidden.forEach(key => {
    const el = grid.querySelector('[data-widget="' + key + '"]');
    if (el) el.classList.add('hidden');
  });
})();

// ── SortableJS ────────────────────────────────────────────────────────
let sortable = null;

function initSortable() {
  sortable = Sortable.create(document.getElementById('widget-grid'), {
    animation: 180,
    handle: '.widget-drag-handle',
    ghostClass: 'opacity-50',
    dragClass: 'shadow-2xl',
    onEnd: function() { /* order collected on save */ },
  });
}

// ── Edit mode toggle ──────────────────────────────────────────────────
let editMode = false;

function toggleEditMode() {
  editMode = !editMode;

  // Show / hide widget controls rows
  document.querySelectorAll('.widget-controls').forEach(el => {
    el.classList.toggle('hidden', !editMode);
    el.classList.toggle('flex',   editMode);
  });

  // Edit / save buttons in toolbar
  document.getElementById('edit-layout-btn').classList.toggle('hidden', editMode);
  document.getElementById('save-layout-btn').classList.toggle('hidden', !editMode);
  document.getElementById('reset-layout-btn').classList.toggle('hidden', !editMode);
  document.getElementById('edit-banner').classList.toggle('hidden', !editMode);
  document.getElementById('hidden-tray').classList.toggle('hidden', !editMode);

  if (editMode) {
    initSortable();
    updateHiddenTray();
  } else {
    if (sortable) { sortable.destroy(); sortable = null; }
  }
}

// ── Hide / restore widget ─────────────────────────────────────────────
function hideWidget(key) {
  const el = document.querySelector('[data-widget="' + key + '"]');
  if (el) el.classList.add('hidden');
  updateHiddenTray();
}

function restoreWidget(key) {
  const el = document.querySelector('[data-widget="' + key + '"]');
  if (el) el.classList.remove('hidden');
  updateHiddenTray();
}

function updateHiddenTray() {
  const tray   = document.getElementById('hidden-tray-items');
  const hidden = Array.from(document.querySelectorAll('.widget-wrap.hidden'))
                      .map(el => el.dataset.widget);

  tray.innerHTML = hidden.map(key => `
    <button onclick="restoreWidget('${key}')"
      class="inline-flex items-center gap-1.5 bg-white border border-line text-ink/70 text-xs font-bold px-3 py-1.5 rounded-full hover:border-brand-300 hover:text-brand-700 transition-colors">
      <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M10 4v12M4 10h12"/>
      </svg>
      ${WIDGET_LABELS[key] || key}
    </button>
  `).join('');
}

// ── Save layout ───────────────────────────────────────────────────────
async function saveLayout() {
  const grid   = document.getElementById('widget-grid');
  const order  = Array.from(grid.querySelectorAll('.widget-wrap:not(.hidden)')).map(el => el.dataset.widget);
  const hidden = Array.from(grid.querySelectorAll('.widget-wrap.hidden')).map(el => el.dataset.widget);

  const btn = document.getElementById('save-layout-btn');
  btn.textContent = 'Saving…';
  btn.disabled    = true;

  try {
    const res = await fetch('/api/dashboard/layout', {
      method:  'PUT',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body:    JSON.stringify({ order, hidden }),
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('Save failed');
    toggleEditMode();
  } catch (e) {
    btn.textContent = 'Save layout';
    btn.disabled    = false;
    alert('Could not save layout. Please try again.');
  }
}

// ── Reset layout ──────────────────────────────────────────────────────
async function resetLayout() {
  if (!confirm('Reset to default widget layout?')) return;

  try {
    const res = await fetch('/api/dashboard/layout/reset', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('Reset failed');
    window.location.reload();
  } catch (e) {
    alert('Could not reset layout. Please try again.');
  }
}

// ── Export dropdown ───────────────────────────────────────────────────
function toggleExport(event) {
  event.stopPropagation();
  const panel = document.getElementById('export-panel');
  panel.classList.toggle('hidden');
}
document.addEventListener('click', () => {
  document.getElementById('export-panel')?.classList.add('hidden');
});

// ── KPI Sparklines ────────────────────────────────────────────────────
function sparkline(id, data, color) {
  const el = document.getElementById(id);
  if (!el || !data.length) return;
  new Chart(el, {
    type: 'line',
    data: {
      labels: data.map((_, i) => i),
      datasets: [{ data, borderColor: color, backgroundColor: color + '22', borderWidth: 2,
        fill: true, tension: 0.4, pointRadius: 0 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { enabled: false } },
      scales: { x: { display: false }, y: { display: false } },
      animation: { duration: 0 },
    }
  });
}

if (TIME_SERIES.length > 0) {
  sparkline('wSparkReach',      TIME_SERIES.map(r => r.reach||0),    '#65a1d8');
  sparkline('wSparkEngagement', TIME_SERIES.map(r => (r.likes||0)+(r.comments||0)+(r.shares||0)), '#2f76bd');
  sparkline('wSparkClicks',     TIME_SERIES.map(r => r.clicks||0),   '#021b2e');
  sparkline('wSparkFollowers',  TIME_SERIES.map(r => r.reach||0),    '#8bb4dc');
}

// ── Time series chart ─────────────────────────────────────────────────
const tsCtx = document.getElementById('wChartTimeSeries');
let tsChart = null;

if (tsCtx && TIME_SERIES.length > 0) {
  const labels   = TIME_SERIES.map(r => r.period);
  const datasets = {
    engagement: TIME_SERIES.map(r => (r.likes||0)+(r.comments||0)+(r.shares||0)+(r.saves||0)),
    reach:      TIME_SERIES.map(r => r.reach||0),
    clicks:     TIME_SERIES.map(r => r.clicks||0),
  };

  tsChart = new Chart(tsCtx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Engagement',
        data:  datasets.engagement,
        borderColor:     '#65a1d8',
        backgroundColor: 'rgba(101,161,216,0.12)',
        borderWidth: 2.5,
        fill: true,
        tension: 0.4,
        pointRadius: 2,
        pointHoverRadius: 5,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
      scales: {
        x: { grid: { color: '#e3e9ee' }, ticks: { color: '#021b2e66', font: { size: 11 } } },
        y: { grid: { color: '#e3e9ee' }, ticks: { color: '#021b2e66', font: { size: 11 } } },
      }
    }
  });
} else if (tsCtx) {
  tsCtx.parentElement.innerHTML = '<div class="h-60 flex items-center justify-center text-ink/30 text-sm">No time series data for this period</div>';
}

window.wSwitchMetric = function(metric) {
  if (!tsChart) return;
  document.querySelectorAll('[id^="wBtn-"]').forEach(b => {
    b.className = 'px-2.5 py-1 rounded-md text-ink/50 hover:text-ink transition-colors';
  });
  document.getElementById('wBtn-' + metric).className = 'px-2.5 py-1 rounded-md bg-brand-50 text-brand-700 transition-colors';

  const colors = { engagement: '#65a1d8', reach: '#2f76bd', clicks: '#021b2e' };
  const map    = { engagement: 'Engagement', reach: 'Reach', clicks: 'Clicks' };
  const data   = TIME_SERIES.map(r => {
    if (metric === 'engagement') return (r.likes||0)+(r.comments||0)+(r.shares||0)+(r.saves||0);
    if (metric === 'reach')      return r.reach||0;
    if (metric === 'clicks')     return r.clicks||0;
    return 0;
  });

  tsChart.data.datasets[0].data            = data;
  tsChart.data.datasets[0].label           = map[metric];
  tsChart.data.datasets[0].borderColor     = colors[metric];
  tsChart.data.datasets[0].backgroundColor = colors[metric] + '1f';
  tsChart.update();
};

// ── Platform donut ─────────────────────────────────────────────────────
const donutEl = document.getElementById('wDonutChart');
if (donutEl && BY_PLATFORM.length > 0) {
  const platformColors = {
    instagram:'#65a1d8', facebook:'#021b2e', twitter:'#2f76bd',
    linkedin:'#8bb4dc',  tiktok:'#333',      youtube:'#FF0000', threads:'#555',
  };
  new Chart(donutEl, {
    type: 'doughnut',
    data: {
      labels:   BY_PLATFORM.map(p => p.platform),
      datasets: [{
        data:            BY_PLATFORM.map(p => p.engagement),
        backgroundColor: BY_PLATFORM.map(p => platformColors[p.platform?.toLowerCase()] || '#aaa'),
        borderWidth: 2,
        borderColor: '#fff',
      }]
    },
    options: {
      responsive: false,
      cutout: '68%',
      plugins: { legend: { display: false }, tooltip: { enabled: true } },
    }
  });
}
</script>
@endpush
