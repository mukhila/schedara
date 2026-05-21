@extends('layouts.backend')

@section('title', 'Content Approvals')

@section('content')
<div class="max-w-5xl mx-auto">

  {{-- Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-xl font-bold text-ink">Content Approvals</h1>
      <p class="text-sm text-ink/40 mt-0.5">Review and approve media files submitted by your team.</p>
    </div>
    <a href="{{ route('cms.index') }}"
       class="flex items-center gap-1.5 text-sm text-ink/50 hover:text-ink transition-colors">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Library
    </a>
  </div>

  @if(session('success'))
    <div class="mb-5 px-4 py-3 rounded-xl bg-mint/10 text-mint text-sm border border-mint/20">{{ session('success') }}</div>
  @endif

  @if($pending->isEmpty())
    <div class="card flex flex-col items-center justify-center py-20 text-center">
      <svg class="w-14 h-14 text-mint/40 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <p class="text-sm font-semibold text-ink/60">All caught up!</p>
      <p class="text-xs text-ink/30 mt-1">No files are pending approval.</p>
    </div>
  @else
    <div class="card overflow-hidden">
      <div class="px-5 py-3 border-b border-line bg-paper flex items-center justify-between">
        <span class="text-sm font-semibold text-ink">{{ $pending->count() }} pending {{ $pending->count() === 1 ? 'item' : 'items' }}</span>
        <button onclick="approveAll()"
                class="text-xs font-bold text-mint hover:text-mint/80 transition-colors">Approve all</button>
      </div>

      <div class="divide-y divide-line">
        @foreach($pending as $approval)
          @php $file = $approval->mediaFile; @endphp
          @if(!$file) @continue @endif
          <div class="p-4 sm:p-5 flex items-start gap-4" id="approval-{{ $approval->id }}" data-uuid="{{ $file->uuid }}" data-id="{{ $approval->id }}">

            {{-- Thumbnail --}}
            <a href="{{ route('cms.show', $file->uuid) }}" class="flex-shrink-0">
              @if($file->isImage())
                <img src="{{ $file->thumbnailPublicUrl() ?? $file->publicUrl() }}"
                     alt="{{ $file->alt_text }}"
                     class="w-16 h-16 rounded-xl object-cover border border-line">
              @elseif($file->isVideo())
                <div class="w-16 h-16 rounded-xl border border-line bg-ink/5 flex items-center justify-center">
                  <svg class="w-7 h-7 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                </div>
              @else
                <div class="w-16 h-16 rounded-xl border border-line bg-ink/5 flex items-center justify-center">
                  <svg class="w-7 h-7 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
              @endif
            </a>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
              <a href="{{ route('cms.show', $file->uuid) }}"
                 class="font-semibold text-ink hover:text-brand-600 transition-colors truncate block">
                {{ $file->name }}
              </a>
              <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1 text-xs text-ink/40">
                <span class="capitalize">{{ $file->type }}</span>
                <span>·</span>
                <span>{{ $file->humanSize() }}</span>
                @if($approval->requester)
                  <span>·</span>
                  <span>Submitted by <strong class="font-medium text-ink/60">{{ $approval->requester->name }}</strong></span>
                @endif
                <span>·</span>
                <span>{{ $approval->created_at->diffForHumans() }}</span>
              </div>

              {{-- Inline comments input --}}
              <div class="mt-2.5">
                <input type="text" id="comment-{{ $approval->id }}"
                       placeholder="Comments (optional for approve, required for reject)…"
                       class="w-full text-sm rounded-lg border border-line px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-400/30 bg-white">
              </div>
            </div>

            {{-- Actions --}}
            <div class="flex-shrink-0 flex flex-col gap-2 pt-1">
              <button onclick="approve({{ $approval->id }}, '{{ $file->uuid }}')"
                      class="flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white rounded-lg bg-mint hover:opacity-90 transition-opacity">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
              </button>
              <button onclick="reject({{ $approval->id }}, '{{ $file->uuid }}')"
                      class="flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-coral rounded-lg border border-coral/20 hover:bg-coral/5 transition-colors">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
              </button>
            </div>

          </div>
        @endforeach
      </div>
    </div>
  @endif

</div>

<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const TENANT_ID = '{{ app("current.tenant")->id }}';

function apiHeaders() {
  return {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Tenant-ID': TENANT_ID};
}

function fadeOut(id) {
  const row = document.getElementById(`approval-${id}`);
  if (!row) return;
  row.style.opacity = '0';
  row.style.transition = 'opacity .3s';
  setTimeout(() => row.remove(), 300);
}

function approve(approvalId, uuid) {
  const comments = document.getElementById(`comment-${approvalId}`)?.value || '';
  fetch(`/api/media/${uuid}/approve`, {
    method: 'POST', headers: apiHeaders(),
    body: JSON.stringify({comments})
  }).then(r => r.ok && fadeOut(approvalId));
}

function reject(approvalId, uuid) {
  const comments = document.getElementById(`comment-${approvalId}`)?.value || '';
  if (!comments) { alert('A rejection reason is required.'); return; }
  fetch(`/api/media/${uuid}/reject`, {
    method: 'POST', headers: apiHeaders(),
    body: JSON.stringify({comments})
  }).then(r => r.ok && fadeOut(approvalId));
}

function approveAll() {
  if (!confirm('Approve all pending items without comments?')) return;
  document.querySelectorAll('[data-uuid]').forEach(el => {
    const uuid = el.dataset.uuid;
    const id   = el.dataset.id;
    fetch(`/api/media/${uuid}/approve`, {
      method: 'POST', headers: apiHeaders(),
      body: JSON.stringify({comments: ''})
    }).then(r => r.ok && fadeOut(id));
  });
}
</script>
@endsection
