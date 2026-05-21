@extends('layouts.backend')
@section('title', 'Analytics Reports')

@section('content')

<div class="flex items-start justify-between gap-4 flex-wrap mb-4">
  <div>
    <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-2">Analytics</div>
    <h1 class="text-3xl font-extrabold tracking-tight text-ink">Reports</h1>
  </div>
  <button onclick="document.getElementById('newReportModal').classList.remove('hidden')"
    class="bg-brand-600 text-white font-semibold text-sm px-4 py-2 rounded-lg hover:bg-brand-700 transition-colors flex items-center gap-1.5">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    Generate Report
  </button>
</div>

@include('backend.analytics._nav')

@if(session('success'))
<div class="mb-4 p-4 bg-mint/10 border border-mint/30 rounded-lg text-mint font-semibold text-sm">
  {{ session('success') }}
</div>
@endif

<div class="card p-6">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-[10px] font-bold uppercase tracking-wider text-ink/40 text-left border-b border-line">
          <th class="py-2 font-bold">Report</th>
          <th class="py-2 font-bold">Type</th>
          <th class="py-2 font-bold">Date range</th>
          <th class="py-2 font-bold">Created by</th>
          <th class="py-2 font-bold">Status</th>
          <th class="py-2 font-bold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        @forelse($reports as $report)
        @php
          $statusPill = match($report->status) {
            'ready'      => 'pill-mint',
            'processing' => 'pill-brand',
            'pending'    => 'pill-gold',
            'failed'     => 'pill-coral',
            default      => '',
          };
        @endphp
        <tr class="hover:bg-paper/80 transition-colors">
          <td class="py-3 font-bold text-ink">{{ $report->name }}</td>
          <td class="py-3 capitalize text-ink/70">{{ str_replace('_', ' ', $report->type) }}</td>
          <td class="py-3 text-ink/70 text-xs">{{ $report->date_from->format('M d, Y') }} – {{ $report->date_to->format('M d, Y') }}</td>
          <td class="py-3 text-ink/70">{{ $report->creator->name ?? 'System' }}</td>
          <td class="py-3"><span class="pill {{ $statusPill }}">{{ ucfirst($report->status) }}</span></td>
          <td class="py-3 text-right">
            @if($report->isReady() && $report->file_url)
            <a href="{{ $report->file_url }}" class="text-brand-600 text-xs font-bold hover:text-brand-800 mr-3">Download</a>
            @endif
            <button
              onclick="deleteReport('{{ $report->uuid }}')"
              class="text-ink/40 hover:text-coral text-xs font-bold transition-colors">Delete</button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="py-12 text-center text-ink/40 text-sm">
            No reports yet.
            <button onclick="document.getElementById('newReportModal').classList.remove('hidden')"
              class="text-brand-600 font-semibold hover:underline">Generate your first report</button>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $reports->links() }}
</div>

{{-- New report modal --}}
<div id="newReportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-ink/40">
  <div class="card p-6 w-full max-w-lg mx-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold">Generate Report</h3>
      <button onclick="document.getElementById('newReportModal').classList.add('hidden')" class="text-ink/40 hover:text-ink">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="POST" action="{{ route('analytics.reports.create') }}">
      @csrf
      <div class="space-y-3">
        <div>
          <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Report name</label>
          <input type="text" name="name" required class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400"
            placeholder="e.g. Monthly Performance – May 2026">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Type</label>
            <select name="type" class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
              @foreach(['custom','engagement','follower','campaign','roi','demographic'] as $t)
              <option value="{{ $t }}">{{ ucfirst($t) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Format</label>
            <select name="format" class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
              <option value="pdf">PDF</option>
              <option value="csv">CSV</option>
              <option value="xlsx">Excel</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Date from</label>
            <input type="date" name="date_from" required value="{{ now()->subDays(29)->toDateString() }}"
              class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
          </div>
          <div>
            <label class="text-xs font-bold text-ink/60 uppercase tracking-wider">Date to</label>
            <input type="date" name="date_to" required value="{{ now()->toDateString() }}"
              class="mt-1 w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-400">
          </div>
        </div>
      </div>
      <div class="mt-5 flex gap-2 justify-end">
        <button type="button" onclick="document.getElementById('newReportModal').classList.add('hidden')"
          class="px-4 py-2 text-sm font-semibold text-ink/60 hover:text-ink border border-line rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-semibold bg-brand-600 text-white rounded-lg hover:bg-brand-700">Queue Report</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
const TENANT_ID = '{{ app("current.tenant")->id }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function deleteReport(uuid) {
  if (!confirm('Delete this report?')) return;
  const res = await fetch('/api/analytics/reports/' + uuid, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Tenant-ID': TENANT_ID },
  });
  if (res.ok) location.reload();
}
</script>
@endpush
