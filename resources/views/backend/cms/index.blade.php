@extends('layouts.backend')

@section('title', 'Media Library')

@section('content')
<div class="flex h-[calc(100vh-0px)] overflow-hidden">

  {{-- Folder Sidebar --}}
  <aside class="w-56 flex-shrink-0 border-r border-line bg-white flex flex-col overflow-y-auto">
    <div class="p-4 border-b border-line flex items-center justify-between">
      <span class="text-xs font-bold text-ink/50 uppercase tracking-wider">Folders</span>
      <button onclick="openNewFolderModal()" class="text-ink/30 hover:text-brand-600 transition-colors" title="New folder">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </button>
    </div>

    <nav class="p-2 flex-1">
      <a href="{{ route('cms.index') }}"
         class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm transition-colors {{ !request('folder_id') && !request('favorites') ? 'bg-brand-50 text-brand-700 font-medium' : 'text-ink/60 hover:bg-paper' }}">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        All Media
        <span class="ml-auto text-xs text-ink/40">{{ $stats['total'] }}</span>
      </a>
      <a href="{{ route('cms.index', ['favorites' => 1]) }}"
         class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm transition-colors {{ request('favorites') ? 'bg-brand-50 text-brand-700 font-medium' : 'text-ink/60 hover:bg-paper' }}">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Favorites
        <span class="ml-auto text-xs text-ink/40">{{ $stats['favorites'] }}</span>
      </a>

      <div class="mt-3 mb-1 px-2 text-[10px] font-bold text-ink/30 uppercase tracking-wider">Folders</div>

      @forelse($folders as $folder)
        @include('backend.cms._folder_item', ['folder' => $folder, 'depth' => 0])
      @empty
        <p class="text-xs text-ink/30 px-2 py-1">No folders yet</p>
      @endforelse
    </nav>

    <div class="p-4 border-t border-line">
      @php $usedGb = round($stats['total_size'] / 1073741824, 2); @endphp
      <div class="text-xs text-ink/40 mb-1">Storage used</div>
      <div class="h-1.5 bg-line rounded-full overflow-hidden">
        <div class="h-full bg-brand-500 rounded-full" style="width:{{ min(100, $usedGb * 10) }}%"></div>
      </div>
      <div class="text-xs text-ink/40 mt-1">{{ $usedGb }} GB</div>
    </div>
  </aside>

  {{-- Main content --}}
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    {{-- Toolbar --}}
    <div class="border-b border-line bg-white px-5 py-3 flex items-center gap-3 flex-shrink-0">
      <div class="relative flex-1 max-w-xs">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <form method="GET">
          @foreach(request()->except('search') as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
          @endforeach
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Search media…"
                 class="w-full pl-9 pr-4 py-1.5 text-sm rounded-lg border border-line focus:outline-none focus:ring-2 focus:ring-brand-400/30 bg-white">
        </form>
      </div>

      {{-- Type filter --}}
      <div class="flex items-center gap-1">
        @foreach(['', 'image', 'video', 'document', 'audio'] as $t)
          <a href="{{ route('cms.index', array_merge(request()->query(), ['type' => $t ?: null])) }}"
             class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ request('type', '') === $t ? 'bg-brand-100 text-brand-700' : 'text-ink/50 hover:bg-line/50' }}">
            {{ $t ? ucfirst($t) : 'All' }}
          </a>
        @endforeach
      </div>

      {{-- View toggle --}}
      <div class="ml-auto flex items-center gap-2">
        <button id="view-grid" onclick="setView('grid')" class="p-1.5 rounded text-ink/40 hover:text-ink transition-colors" title="Grid view">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </button>
        <button id="view-list" onclick="setView('list')" class="p-1.5 rounded text-ink/40 hover:text-ink transition-colors" title="List view">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </button>
        <button onclick="document.getElementById('upload-modal').classList.remove('hidden')"
                class="ml-2 px-3 py-1.5 rounded-lg text-sm font-semibold text-white flex items-center gap-1.5 transition-opacity hover:opacity-90" style="background:#4a8ccc">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Upload
        </button>
      </div>
    </div>

    {{-- Stats bar --}}
    <div class="border-b border-line bg-paper px-5 py-2 flex items-center gap-6 text-xs text-ink/50 flex-shrink-0">
      <span>{{ $stats['total'] }} files</span>
      <span>{{ $stats['images'] }} images</span>
      <span>{{ $stats['videos'] }} videos</span>
      <span>{{ $stats['documents'] }} documents</span>
      @if($stats['pending'] > 0)
        <a href="{{ route('cms.approvals') }}" class="text-gold font-medium">{{ $stats['pending'] }} pending approval</a>
      @endif
      @if($currentFolder)
        <span class="ml-auto flex items-center gap-1">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          {{ $currentFolder->name }}
        </span>
      @endif
    </div>

    {{-- Media grid --}}
    <div class="flex-1 overflow-y-auto p-5">
      @if(session('success'))
        <div class="mb-4 px-4 py-2.5 rounded-lg bg-mint/10 text-mint text-sm border border-mint/20">{{ session('success') }}</div>
      @endif

      @if($media->isEmpty())
        <div class="flex flex-col items-center justify-center h-full py-20 text-center">
          <svg class="w-16 h-16 text-ink/10 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          <p class="text-sm text-ink/40 mb-4">No media files found.</p>
          <button onclick="document.getElementById('upload-modal').classList.remove('hidden')"
                  class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#4a8ccc">
            Upload your first file
          </button>
        </div>
      @else
        {{-- Grid view --}}
        <div id="grid-view" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
          @foreach($media as $file)
            <div class="group relative bg-white rounded-xl border border-line overflow-hidden cursor-pointer hover:border-brand-300 hover:shadow-md transition-all"
                 onclick="openPreview('{{ $file->uuid }}')">
              {{-- Thumbnail --}}
              <div class="aspect-square bg-paper flex items-center justify-center overflow-hidden">
                @if($file->isImage() && $file->thumbnailPublicUrl())
                  <img src="{{ $file->thumbnailPublicUrl() }}" alt="{{ $file->alt_text }}" class="w-full h-full object-cover">
                @elseif($file->isImage())
                  <img src="{{ $file->publicUrl() }}" alt="{{ $file->alt_text }}" class="w-full h-full object-cover">
                @elseif($file->isVideo())
                  <div class="flex flex-col items-center gap-1">
                    <svg class="w-8 h-8 text-ink/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                    @if($file->humanDuration())
                      <span class="text-xs text-ink/40">{{ $file->humanDuration() }}</span>
                    @endif
                  </div>
                @elseif($file->isDocument())
                  <svg class="w-8 h-8 text-ink/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                @else
                  <svg class="w-8 h-8 text-ink/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                @endif

                {{-- Status badges --}}
                <div class="absolute top-1.5 left-1.5 flex gap-1">
                  @if($file->optimization_status === 'processing' || $file->compression_status === 'processing')
                    <span class="text-[9px] px-1 py-0.5 rounded bg-gold/90 text-white font-bold">Processing</span>
                  @endif
                  @if($file->approval_status === 'pending')
                    <span class="text-[9px] px-1 py-0.5 rounded bg-coral/90 text-white font-bold">Pending</span>
                  @endif
                </div>

                {{-- Favorite --}}
                <form method="POST" action="{{ route('cms.favorite', $file->uuid) }}" class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                  @csrf
                  <button class="{{ $file->is_favorite ? 'text-gold' : 'text-white/80 hover:text-gold' }} drop-shadow transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="{{ $file->is_favorite ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                  </button>
                </form>
              </div>

              <div class="p-2">
                <p class="text-xs font-medium text-ink truncate">{{ $file->name }}</p>
                <p class="text-[10px] text-ink/40">{{ $file->humanSize() }}</p>
              </div>
            </div>
          @endforeach
        </div>

        {{-- List view (hidden by default) --}}
        <div id="list-view" class="hidden">
          <div class="bg-white rounded-2xl border border-line overflow-hidden">
            <table class="w-full text-sm">
              <thead><tr class="border-b border-line bg-paper">
                <th class="text-left px-4 py-2 font-semibold text-ink/50 text-xs">Name</th>
                <th class="text-left px-4 py-2 font-semibold text-ink/50 text-xs">Type</th>
                <th class="text-left px-4 py-2 font-semibold text-ink/50 text-xs">Size</th>
                <th class="text-left px-4 py-2 font-semibold text-ink/50 text-xs">Status</th>
                <th class="text-left px-4 py-2 font-semibold text-ink/50 text-xs">Uploaded</th>
                <th class="w-8"></th>
              </tr></thead>
              <tbody class="divide-y divide-line">
                @foreach($media as $file)
                <tr class="hover:bg-paper/70 transition-colors cursor-pointer" onclick="openPreview('{{ $file->uuid }}')">
                  <td class="px-4 py-2.5 flex items-center gap-2">
                    @if($file->isImage() && $file->thumbnailPublicUrl())
                      <img src="{{ $file->thumbnailPublicUrl() }}" class="w-8 h-8 rounded object-cover">
                    @else
                      <div class="w-8 h-8 rounded bg-line/50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                      </div>
                    @endif
                    <span class="font-medium text-ink truncate max-w-[200px]">{{ $file->name }}</span>
                  </td>
                  <td class="px-4 py-2.5 text-ink/50 capitalize">{{ $file->type }}</td>
                  <td class="px-4 py-2.5 text-ink/50">{{ $file->humanSize() }}</td>
                  <td class="px-4 py-2.5">
                    <span class="text-xs px-1.5 py-0.5 rounded {{ $file->approval_status === 'approved' ? 'bg-mint/10 text-mint' : ($file->approval_status === 'pending' ? 'bg-gold/20 text-yellow-700' : 'bg-line/60 text-ink/50') }}">
                      {{ ucfirst($file->approval_status) }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 text-ink/40 text-xs">{{ $file->created_at->diffForHumans() }}</td>
                  <td class="px-4 py-2.5" onclick="event.stopPropagation()">
                    <a href="{{ route('cms.show', $file->uuid) }}" class="text-ink/30 hover:text-ink">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
          <p class="text-xs text-ink/40">{{ $media->total() }} items</p>
          {{ $media->withQueryString()->links() }}
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Upload Modal --}}
<div id="upload-modal" class="hidden fixed inset-0 bg-ink/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-ink">Upload Files</h3>
      <button onclick="document.getElementById('upload-modal').classList.add('hidden')" class="text-ink/30 hover:text-ink">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <form method="POST" action="{{ route('cms.upload') }}" enctype="multipart/form-data" id="upload-form">
      @csrf
      @if($currentFolder)
        <input type="hidden" name="folder_id" value="{{ $currentFolder->uuid }}">
      @endif

      {{-- Drop zone --}}
      <div id="drop-zone"
           class="border-2 border-dashed border-line rounded-xl p-8 text-center cursor-pointer hover:border-brand-400 transition-colors mb-4"
           ondragover="event.preventDefault()" ondrop="handleDrop(event)" onclick="document.getElementById('file-input').click()">
        <svg class="w-10 h-10 mx-auto text-ink/20 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p class="text-sm text-ink/50">Drag & drop files here or <span class="text-brand-600">browse</span></p>
        <p class="text-xs text-ink/30 mt-1">Images, videos, PDFs, documents — max 200MB each</p>
        <input type="file" id="file-input" name="files[]" multiple class="hidden"
               accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.mp4,.mov,.avi,.webm,.pdf,.doc,.docx,.xls,.xlsx,.mp3,.wav"
               onchange="previewFiles(this)">
      </div>

      <div id="file-preview" class="hidden mb-4 space-y-1 max-h-32 overflow-y-auto"></div>

      <div class="grid grid-cols-2 gap-3 mb-4">
        <div>
          <label class="block text-xs text-ink/50 mb-1">Alt text (optional)</label>
          <input type="text" name="alt_text" placeholder="Describe the image…" class="w-full text-sm rounded-lg border border-line px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-400/30">
        </div>
        <div>
          <label class="block text-xs text-ink/50 mb-1">Tags (comma separated)</label>
          <input type="text" name="tags_raw" id="tags-raw" placeholder="design, banner, 2026…" class="w-full text-sm rounded-lg border border-line px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-400/30" oninput="parseTags(this.value)">
          <div id="tags-hidden"></div>
        </div>
      </div>

      <label class="flex items-center gap-2 text-sm text-ink/60 mb-4 cursor-pointer">
        <input type="checkbox" name="request_approval" value="1" class="rounded border-line">
        Submit for approval after upload
      </label>

      <div class="flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')"
                class="px-4 py-2 text-sm rounded-lg border border-line hover:bg-line/40 transition-colors">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white transition-opacity hover:opacity-90" style="background:#4a8ccc">Upload</button>
      </div>
    </form>
  </div>
</div>

{{-- New Folder Modal --}}
<div id="folder-modal" class="hidden fixed inset-0 bg-ink/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
    <h3 class="font-bold text-ink mb-4">New Folder</h3>
    <input type="text" id="folder-name" placeholder="Folder name…" class="w-full text-sm rounded-xl border border-line px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-400/30 mb-4">
    <div class="flex justify-end gap-2">
      <button onclick="document.getElementById('folder-modal').classList.add('hidden')"
              class="px-4 py-2 text-sm rounded-lg border border-line hover:bg-line/40 transition-colors">Cancel</button>
      <button onclick="createFolder()" class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition-opacity hover:opacity-90" style="background:#4a8ccc">Create</button>
    </div>
  </div>
</div>

{{-- Preview Overlay --}}
<div id="preview-overlay" class="hidden fixed inset-0 bg-ink/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="closePreview()">
  <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex overflow-hidden" onclick="event.stopPropagation()">
    <div class="flex-1 bg-ink/90 flex items-center justify-center p-4" id="preview-media">
      <p class="text-white/50 text-sm">Loading…</p>
    </div>
    <div class="w-72 flex-shrink-0 flex flex-col">
      <div class="p-4 border-b border-line flex items-center justify-between">
        <h3 class="font-semibold text-ink text-sm truncate" id="preview-name">—</h3>
        <button onclick="closePreview()" class="text-ink/30 hover:text-ink ml-2">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-4 space-y-3 text-sm" id="preview-details"></div>
      <div class="p-4 border-t border-line">
        <a id="preview-view-link" href="#" class="block w-full py-2 text-center text-sm font-semibold text-white rounded-lg transition-opacity hover:opacity-90" style="background:#4a8ccc">View Details</a>
      </div>
    </div>
  </div>
</div>

<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const TENANT_ID = '{{ app("current.tenant")->id }}';

function apiHeaders(extra = {}) {
  return {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Tenant-ID': TENANT_ID, ...extra};
}

// View toggle
let currentView = localStorage.getItem('cms_view') || 'grid';
function setView(v) {
  currentView = v;
  localStorage.setItem('cms_view', v);
  document.getElementById('grid-view').classList.toggle('hidden', v !== 'grid');
  document.getElementById('list-view').classList.toggle('hidden', v !== 'list');
}
setView(currentView);

// Folder modal
function openNewFolderModal() {
  document.getElementById('folder-modal').classList.remove('hidden');
  document.getElementById('folder-name').focus();
}

function createFolder() {
  const name = document.getElementById('folder-name').value.trim();
  if (!name) return;

  fetch('/api/media/folders', {
    method: 'POST', headers: apiHeaders(),
    body: JSON.stringify({name})
  }).then(r => r.json()).then(() => window.location.reload());
}

// File drop
function handleDrop(e) {
  e.preventDefault();
  const input = document.getElementById('file-input');
  const dt = new DataTransfer();
  Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
  input.files = dt.files;
  previewFiles(input);
}

function previewFiles(input) {
  const preview = document.getElementById('file-preview');
  preview.innerHTML = '';
  preview.classList.remove('hidden');
  Array.from(input.files).forEach(f => {
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 text-xs text-ink/60 p-1.5 bg-paper rounded';
    div.innerHTML = `<svg class="w-3.5 h-3.5 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>${f.name} <span class="ml-auto">${(f.size/1048576).toFixed(1)} MB</span>`;
    preview.appendChild(div);
  });
}

// Tags
function parseTags(raw) {
  const container = document.getElementById('tags-hidden');
  container.innerHTML = '';
  raw.split(',').map(t => t.trim()).filter(Boolean).forEach(tag => {
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'tags[]';
    inp.value = tag;
    container.appendChild(inp);
  });
}

// Preview modal
function openPreview(uuid) {
  document.getElementById('preview-overlay').classList.remove('hidden');
  document.getElementById('preview-media').innerHTML = '<p class="text-white/40 text-sm">Loading…</p>';
  document.getElementById('preview-details').innerHTML = '';

  fetch(`/api/media/${uuid}`, {headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Tenant-ID': TENANT_ID}})
    .then(r => r.json())
    .then(({data}) => {
      document.getElementById('preview-name').textContent = data.name;
      document.getElementById('preview-view-link').href = `/cms/${data.uuid}`;

      let mediaHtml = '';
      if (data.type === 'image') {
        mediaHtml = `<img src="${data.url}" class="max-w-full max-h-full object-contain rounded" alt="${data.alt_text || ''}">`;
      } else if (data.type === 'video') {
        mediaHtml = `<video src="${data.url}" controls class="max-w-full max-h-full rounded"></video>`;
      } else {
        mediaHtml = `<div class="text-center text-white/40"><svg class="w-16 h-16 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg><p>${data.name}</p></div>`;
      }
      document.getElementById('preview-media').innerHTML = mediaHtml;

      document.getElementById('preview-details').innerHTML = `
        <div><p class="text-xs text-ink/40">Size</p><p class="font-medium">${data.human_size}</p></div>
        <div><p class="text-xs text-ink/40">Type</p><p class="font-medium capitalize">${data.type}</p></div>
        ${data.width ? `<div><p class="text-xs text-ink/40">Dimensions</p><p class="font-medium">${data.width}×${data.height}</p></div>` : ''}
        <div><p class="text-xs text-ink/40">Approval</p><p class="font-medium capitalize">${data.approval_status}</p></div>
        ${data.tags?.length ? `<div><p class="text-xs text-ink/40 mb-1">Tags</p><div class="flex flex-wrap gap-1">${data.tags.map(t=>`<span class="text-xs px-2 py-0.5 rounded-full bg-line/60">${t.name}</span>`).join('')}</div></div>` : ''}
        ${data.alt_text ? `<div><p class="text-xs text-ink/40">Alt text</p><p class="text-xs text-ink/70">${data.alt_text}</p></div>` : ''}
      `;
    });
}

function closePreview() {
  document.getElementById('preview-overlay').classList.add('hidden');
}
</script>
@endsection
