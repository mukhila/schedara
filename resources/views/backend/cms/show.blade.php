@extends('layouts.backend')

@section('title', $media->name . ' · Media Library')

@section('content')
<div class="max-w-6xl mx-auto">

  {{-- Breadcrumb --}}
  <div class="flex items-center gap-2 text-sm text-ink/40 mb-5">
    <a href="{{ route('cms.index') }}" class="hover:text-ink transition-colors">Media Library</a>
    @if($media->folder)
      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <a href="{{ route('cms.index', ['folder_id' => $media->folder->uuid]) }}" class="hover:text-ink transition-colors">{{ $media->folder->name }}</a>
    @endif
    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span class="text-ink font-medium truncate max-w-xs">{{ $media->name }}</span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Left: Preview --}}
    <div class="lg:col-span-2 space-y-4">
      <div class="card p-2 flex items-center justify-center min-h-[360px] bg-ink/5 rounded-2xl overflow-hidden">
        @if($media->isImage())
          <img src="{{ $media->publicUrl() }}" alt="{{ $media->alt_text }}"
               class="max-w-full max-h-[480px] object-contain rounded-xl">
        @elseif($media->isVideo())
          <video src="{{ $media->publicUrl() }}" controls
                 class="max-w-full max-h-[480px] rounded-xl"></video>
        @elseif($media->isAudio())
          <div class="text-center py-10">
            <svg class="w-16 h-16 mx-auto text-ink/20 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
            <audio src="{{ $media->publicUrl() }}" controls class="mt-4"></audio>
          </div>
        @else
          <div class="text-center py-10">
            <svg class="w-16 h-16 mx-auto text-ink/20 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
            <p class="text-sm text-ink/40">{{ strtoupper($media->extension) }} Document</p>
          </div>
        @endif
      </div>

      {{-- Actions --}}
      <div class="flex flex-wrap gap-2">
        <a href="{{ $media->publicUrl() }}" download="{{ $media->original_name }}"
           class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-lg transition-opacity hover:opacity-90" style="background:#4a8ccc">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Download
        </a>

        <form method="POST" action="{{ route('cms.favorite', $media->uuid) }}">
          @csrf
          <button class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg border transition-colors {{ $media->is_favorite ? 'text-gold border-gold/30 bg-gold/10' : 'text-ink/60 border-line hover:bg-paper' }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="{{ $media->is_favorite ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            {{ $media->is_favorite ? 'Unfavorite' : 'Favorite' }}
          </button>
        </form>

        <button onclick="generateShareLink()"
                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-ink/60 rounded-lg border border-line hover:bg-paper transition-colors">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 10.8l6.8-4M8.6 13.2l6.8 4"/></svg>
          Share Link
        </button>

        @if($media->isImage())
          <button onclick="triggerOptimize()"
                  class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-ink/60 rounded-lg border border-line hover:bg-paper transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Optimize
          </button>
          <button onclick="triggerAiTag()"
                  class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-ink/60 rounded-lg border border-line hover:bg-paper transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 1 0 10 10H12V2z"/><path d="M12 2a10 10 0 0 1 10 10"/><path d="M12 12l-3 7.5M12 12l3 7.5M12 12l7.5-3M12 12l7.5 3"/></svg>
            AI Tag
          </button>
        @endif

        <form method="POST" action="{{ route('cms.destroy', $media->uuid) }}"
              onsubmit="return confirm('Delete this file permanently?')" class="ml-auto">
          @csrf
          @method('DELETE')
          <button class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-coral rounded-lg border border-coral/20 hover:bg-coral/5 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            Delete
          </button>
        </form>
      </div>

      {{-- Share link result --}}
      <div id="share-result" class="hidden p-3 bg-brand-50 border border-brand-200 rounded-xl">
        <p class="text-xs text-ink/50 mb-1.5">Share link (expires in 72 hours)</p>
        <div class="flex gap-2">
          <input id="share-url" type="text" readonly class="flex-1 text-sm bg-white border border-line rounded-lg px-3 py-1.5 text-ink/70">
          <button onclick="copyShareLink()" class="px-3 py-1.5 text-sm font-semibold text-white rounded-lg" style="background:#4a8ccc">Copy</button>
        </div>
      </div>

      {{-- Version history --}}
      @if($media->versions->isNotEmpty())
        <div class="card p-4">
          <h3 class="font-semibold text-sm text-ink mb-3">Version History</h3>
          <div class="space-y-2">
            @foreach($media->versions as $v)
              <div class="flex items-center gap-3 py-2 border-b border-line last:border-0">
                <span class="text-xs font-bold text-ink/40 w-8 flex-shrink-0">v{{ $v->version }}</span>
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-ink">{{ $v->change_note ?: 'No note' }}</p>
                  <p class="text-xs text-ink/40">{{ $v->created_at->diffForHumans() }}{{ $v->creator ? ' · ' . $v->creator->name : '' }}</p>
                </div>
                <span class="text-xs text-ink/40">{{ number_format($v->file_size / 1024, 1) }} KB</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Activity log --}}
      @if($media->activityLogs->isNotEmpty())
        <div class="card p-4">
          <h3 class="font-semibold text-sm text-ink mb-3">Activity</h3>
          <div class="space-y-2">
            @foreach($media->activityLogs->take(10) as $log)
              <div class="flex items-start gap-2.5 text-xs text-ink/60">
                <div class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0 {{ $log->status === 'success' ? 'bg-mint' : 'bg-coral' }}"></div>
                <span class="capitalize flex-1">{{ str_replace('_', ' ', $log->action) }}</span>
                <span class="text-ink/30">{{ $log->created_at->diffForHumans() }}</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>

    {{-- Right: Metadata + Approval --}}
    <div class="space-y-4">

      {{-- File Info --}}
      <div class="card p-5">
        <h3 class="font-semibold text-sm text-ink mb-4">File Info</h3>
        <dl class="space-y-3 text-sm">
          <div class="flex justify-between">
            <dt class="text-ink/40">Name</dt>
            <dd class="font-medium text-ink text-right truncate max-w-[160px]">{{ $media->name }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-ink/40">Type</dt>
            <dd class="font-medium capitalize">{{ $media->type }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-ink/40">Extension</dt>
            <dd class="font-medium uppercase">{{ $media->extension }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-ink/40">Size</dt>
            <dd class="font-medium">{{ $media->humanSize() }}</dd>
          </div>
          @if($media->width && $media->height)
            <div class="flex justify-between">
              <dt class="text-ink/40">Dimensions</dt>
              <dd class="font-medium">{{ $media->width }}×{{ $media->height }}</dd>
            </div>
          @endif
          @if($media->duration)
            <div class="flex justify-between">
              <dt class="text-ink/40">Duration</dt>
              <dd class="font-medium">{{ $media->humanDuration() }}</dd>
            </div>
          @endif
          <div class="flex justify-between">
            <dt class="text-ink/40">Version</dt>
            <dd class="font-medium">v{{ $media->version }}</dd>
          </div>
          @if($media->folder)
            <div class="flex justify-between">
              <dt class="text-ink/40">Folder</dt>
              <dd class="font-medium">{{ $media->folder->name }}</dd>
            </div>
          @endif
          <div class="flex justify-between">
            <dt class="text-ink/40">Uploaded</dt>
            <dd class="font-medium">{{ $media->created_at->format('M j, Y') }}</dd>
          </div>
        </dl>
      </div>

      {{-- Alt Text --}}
      <div class="card p-5">
        <h3 class="font-semibold text-sm text-ink mb-3">Alt Text</h3>
        <form method="POST" action="{{ route('cms.index') }}" id="alt-form">
          @csrf
          @method('PUT')
          <textarea name="alt_text" rows="3"
                    class="w-full text-sm rounded-xl border border-line px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-400/30 resize-none"
                    placeholder="Describe this file for accessibility…">{{ $media->alt_text }}</textarea>
          <button type="button" onclick="saveAltText()"
                  class="mt-2 w-full py-2 text-sm font-semibold text-white rounded-lg transition-opacity hover:opacity-90" style="background:#4a8ccc">Save</button>
        </form>
      </div>

      {{-- Tags --}}
      <div class="card p-5">
        <h3 class="font-semibold text-sm text-ink mb-3">Tags</h3>
        <div class="flex flex-wrap gap-1.5 mb-3">
          @forelse($media->mediaTags as $tag)
            <span class="flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium"
                  style="background:{{ $tag->color }}20;color:{{ $tag->color }}">
              {{ $tag->tag_name }}
            </span>
          @empty
            <p class="text-xs text-ink/30">No tags yet</p>
          @endforelse
        </div>
        <div class="flex gap-2">
          <input type="text" id="new-tags" placeholder="Add tags, comma-separated…"
                 class="flex-1 text-sm rounded-lg border border-line px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-400/30">
          <button onclick="saveTags()" class="px-3 py-1.5 text-sm font-semibold text-white rounded-lg" style="background:#4a8ccc">Add</button>
        </div>
      </div>

      {{-- Processing Status --}}
      <div class="card p-5">
        <h3 class="font-semibold text-sm text-ink mb-3">Processing</h3>
        <div class="space-y-2 text-sm">
          @if($media->isImage())
            <div class="flex items-center justify-between">
              <span class="text-ink/50">Image Optimization</span>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium
                {{ $media->optimization_status === 'done' ? 'bg-mint/10 text-mint' : ($media->optimization_status === 'processing' ? 'bg-gold/15 text-yellow-700' : 'bg-line/60 text-ink/40') }}">
                {{ ucfirst($media->optimization_status) }}
              </span>
            </div>
          @endif
          @if($media->isVideo())
            <div class="flex items-center justify-between">
              <span class="text-ink/50">Video Compression</span>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium
                {{ $media->compression_status === 'done' ? 'bg-mint/10 text-mint' : ($media->compression_status === 'processing' ? 'bg-gold/15 text-yellow-700' : 'bg-line/60 text-ink/40') }}">
                {{ ucfirst($media->compression_status) }}
              </span>
            </div>
          @endif
        </div>
      </div>

      {{-- Approval --}}
      <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-sm text-ink">Approval Status</h3>
          <span class="text-xs px-2 py-0.5 rounded-full font-medium
            {{ $media->approval_status === 'approved' ? 'bg-mint/10 text-mint' : ($media->approval_status === 'pending' ? 'bg-gold/15 text-yellow-700' : ($media->approval_status === 'rejected' ? 'bg-coral/10 text-coral' : 'bg-line/60 text-ink/40')) }}">
            {{ ucfirst($media->approval_status) }}
          </span>
        </div>

        @php $pendingApproval = $media->approvals->where('status', 'pending')->first(); @endphp

        @if($pendingApproval)
          <p class="text-xs text-ink/50 mb-3">
            Requested by {{ $pendingApproval->requester?->name ?? 'Unknown' }}
            · {{ $pendingApproval->created_at->diffForHumans() }}
          </p>
          <div class="space-y-2">
            <input type="text" id="approval-comments" placeholder="Comments (optional for approve, required for reject)…"
                   class="w-full text-sm rounded-lg border border-line px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-400/30">
            <div class="flex gap-2">
              <button onclick="approveMedia('{{ $media->uuid }}')"
                      class="flex-1 py-2 text-sm font-semibold text-white rounded-lg bg-mint hover:opacity-90 transition-opacity">Approve</button>
              <button onclick="rejectMedia('{{ $media->uuid }}')"
                      class="flex-1 py-2 text-sm font-semibold text-white rounded-lg bg-coral hover:opacity-90 transition-opacity">Reject</button>
            </div>
          </div>
        @elseif($media->approval_status === 'draft')
          <button onclick="requestApproval('{{ $media->uuid }}')"
                  class="w-full py-2 text-sm font-semibold text-ink/60 rounded-lg border border-line hover:bg-paper transition-colors">
            Submit for Approval
          </button>
        @elseif($media->approvals->isNotEmpty())
          @php $lastApproval = $media->approvals->first(); @endphp
          @if($lastApproval->comments)
            <p class="text-xs text-ink/50 bg-paper rounded-lg p-2 mt-1">{{ $lastApproval->comments }}</p>
          @endif
          @if($lastApproval->approver)
            <p class="text-xs text-ink/40 mt-2">By {{ $lastApproval->approver->name }} · {{ $lastApproval->updated_at->diffForHumans() }}</p>
          @endif
        @endif
      </div>

    </div>
  </div>
</div>

<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const TENANT_ID = '{{ app("current.tenant")->id }}';
const mediaUuid = '{{ $media->uuid }}';

function apiHeaders(extra = {}) {
  return {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Tenant-ID': TENANT_ID, ...extra};
}

function generateShareLink() {
  fetch(`/api/media/${mediaUuid}/share-link`, {
    method: 'POST', headers: apiHeaders(),
    body: JSON.stringify({expires_in_hours: 72})
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('share-result').classList.remove('hidden');
    document.getElementById('share-url').value = data.share_url;
  });
}

function copyShareLink() {
  const input = document.getElementById('share-url');
  input.select();
  navigator.clipboard.writeText(input.value);
}

function saveAltText() {
  const val = document.querySelector('textarea[name="alt_text"]').value;
  fetch(`/api/media/${mediaUuid}`, {
    method: 'PUT', headers: apiHeaders(),
    body: JSON.stringify({alt_text: val})
  }).then(r => r.ok && alert('Alt text saved.'));
}

function saveTags() {
  const raw  = document.getElementById('new-tags').value;
  const tags = raw.split(',').map(t => t.trim()).filter(Boolean);
  if (!tags.length) return;
  fetch(`/api/media/${mediaUuid}/tag`, {
    method: 'POST', headers: apiHeaders(),
    body: JSON.stringify({tags})
  }).then(r => r.ok && window.location.reload());
}

function triggerOptimize() {
  fetch(`/api/media/${mediaUuid}/optimize`, {
    method: 'POST', headers: apiHeaders()
  }).then(r => r.json()).then(d => alert(d.message));
}

function triggerAiTag() {
  fetch(`/api/media/${mediaUuid}/ai-tag`, {
    method: 'POST', headers: apiHeaders()
  }).then(r => r.json()).then(d => alert(d.message));
}

function requestApproval(uuid) {
  fetch(`/api/media/${uuid}/request-approval`, {
    method: 'POST', headers: apiHeaders()
  }).then(r => r.ok && window.location.reload());
}

function approveMedia(uuid) {
  const comments = document.getElementById('approval-comments').value;
  fetch(`/api/media/${uuid}/approve`, {
    method: 'POST', headers: apiHeaders(),
    body: JSON.stringify({comments})
  }).then(r => r.ok && window.location.reload());
}

function rejectMedia(uuid) {
  const comments = document.getElementById('approval-comments').value;
  if (!comments) { alert('A reason is required to reject.'); return; }
  fetch(`/api/media/${uuid}/reject`, {
    method: 'POST', headers: apiHeaders(),
    body: JSON.stringify({comments})
  }).then(r => r.ok && window.location.reload());
}
</script>
@endsection
