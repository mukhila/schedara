@extends('layouts.backend')

@section('title', 'Posts')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  {{-- Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-ink">Posts</h1>
      <p class="text-sm text-ink/50 mt-0.5">Schedule and manage your social media content</p>
    </div>
    <div class="flex items-center gap-3">
      <a href="{{ route('posts.calendar') }}" class="px-4 py-2 rounded-lg border border-line text-sm font-medium hover:bg-line/40 transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Calendar
      </a>
      <a href="{{ route('posts.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white flex items-center gap-2 transition-opacity hover:opacity-90" style="background:#4a8ccc">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Post
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-mint/10 text-mint text-sm font-medium border border-mint/20">
      {{ session('success') }}
    </div>
  @endif

  {{-- Filters --}}
  <form method="GET" action="{{ route('posts.index') }}" class="mb-6 flex flex-wrap items-center gap-3">
    <div class="relative flex-1 min-w-[200px]">
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts…"
             class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-line focus:outline-none focus:ring-2 focus:ring-brand-400/30 bg-white">
    </div>

    <select name="status" class="text-sm rounded-lg border border-line px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-brand-400/30">
      <option value="">All statuses</option>
      @foreach(['draft','scheduled','published','failed','partial'] as $s)
        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
      @endforeach
    </select>

    <select name="platform" class="text-sm rounded-lg border border-line px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-brand-400/30">
      <option value="">All platforms</option>
      @foreach(['facebook','instagram','twitter','linkedin','pinterest','youtube','threads'] as $p)
        <option value="{{ $p }}" @selected(request('platform') === $p)>{{ ucfirst($p) }}</option>
      @endforeach
    </select>

    <button type="submit" class="px-4 py-2 text-sm rounded-lg border border-line hover:bg-line/40 transition-colors">Filter</button>
    @if(request()->hasAny(['search','status','platform']))
      <a href="{{ route('posts.index') }}" class="text-sm text-ink/50 hover:text-ink transition-colors">Clear</a>
    @endif

    {{-- Bulk import --}}
    <div class="ml-auto">
      <button type="button" onclick="document.getElementById('bulk-modal').classList.remove('hidden')"
              class="text-sm text-ink/50 hover:text-ink transition-colors flex items-center gap-1.5">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Bulk Import
      </button>
    </div>
  </form>

  {{-- Posts table --}}
  @if($posts->isEmpty())
    <div class="text-center py-20 border border-line border-dashed rounded-2xl">
      <svg class="w-12 h-12 mx-auto text-ink/20 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
      <p class="text-sm text-ink/40 mb-4">No posts yet. Create your first one.</p>
      <a href="{{ route('posts.create') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#4a8ccc">
        New Post
      </a>
    </div>
  @else
    <div class="bg-white rounded-2xl border border-line overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-line bg-paper">
            <th class="text-left px-4 py-3 font-semibold text-ink/60 w-8"><input type="checkbox" class="rounded"></th>
            <th class="text-left px-4 py-3 font-semibold text-ink/60">Content</th>
            <th class="text-left px-4 py-3 font-semibold text-ink/60">Platforms</th>
            <th class="text-left px-4 py-3 font-semibold text-ink/60">Status</th>
            <th class="text-left px-4 py-3 font-semibold text-ink/60">Scheduled</th>
            <th class="text-left px-4 py-3 font-semibold text-ink/60 w-20"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @foreach($posts as $post)
          <tr class="hover:bg-paper/70 transition-colors">
            <td class="px-4 py-3"><input type="checkbox" class="rounded"></td>
            <td class="px-4 py-3">
              <a href="{{ route('posts.show', $post->uuid) }}" class="font-medium text-ink hover:text-brand-600 transition-colors">
                {{ $post->title ?: str($post->content)->limit(60) }}
              </a>
              @if($post->type !== 'text')
                <span class="ml-2 text-xs px-1.5 py-0.5 rounded bg-line/60 text-ink/50">{{ $post->type }}</span>
              @endif
              @if($post->is_evergreen)
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded bg-mint/10 text-mint">evergreen</span>
              @endif
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                @foreach($post->platforms ?? [] as $platform)
                  @php
                    $colors = ['facebook'=>'#1877F2','instagram'=>'#E1306C','twitter'=>'#1DA1F2','linkedin'=>'#0A66C2','pinterest'=>'#E60023','youtube'=>'#FF0000','threads'=>'#555'];
                    $c = $colors[$platform] ?? '#888';
                  @endphp
                  <span class="w-5 h-5 rounded-full flex items-center justify-center text-white text-[9px] font-bold" style="background:{{ $c }}">
                    {{ strtoupper($platform[0]) }}
                  </span>
                @endforeach
              </div>
            </td>
            <td class="px-4 py-3">
              @php
                $statusColors = ['draft'=>'bg-line text-ink/50','scheduled'=>'bg-brand-100 text-brand-700','queued'=>'bg-gold/20 text-yellow-700','publishing'=>'bg-mint/10 text-mint','published'=>'bg-mint/10 text-mint','failed'=>'bg-coral/10 text-coral','partial'=>'bg-gold/20 text-yellow-700'];
              @endphp
              <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statusColors[$post->status] ?? '' }}">
                {{ ucfirst($post->status) }}
              </span>
            </td>
            <td class="px-4 py-3 text-ink/50">
              {{ $post->scheduled_at ? $post->scheduled_at->format('M d, H:i') : '—' }}
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <a href="{{ route('posts.edit', $post->uuid) }}" class="text-ink/30 hover:text-ink transition-colors" title="Edit">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" action="{{ route('posts.destroy', $post->uuid) }}" onsubmit="return confirm('Delete this post?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-ink/30 hover:text-coral transition-colors" title="Delete">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <div class="px-4 py-3 border-t border-line flex items-center justify-between">
        <p class="text-sm text-ink/50">{{ $posts->total() }} posts</p>
        {{ $posts->withQueryString()->links() }}
      </div>
    </div>
  @endif
</div>

{{-- Bulk Import Modal --}}
<div id="bulk-modal" class="hidden fixed inset-0 bg-ink/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-ink">Bulk Import CSV</h3>
      <button onclick="document.getElementById('bulk-modal').classList.add('hidden')" class="text-ink/30 hover:text-ink">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <p class="text-sm text-ink/60 mb-4">Upload a CSV file to schedule multiple posts at once.</p>
    <form method="POST" action="{{ route('posts.bulk-import') }}" enctype="multipart/form-data">
      @csrf
      <div class="mb-4">
        <label class="block text-sm font-medium text-ink/70 mb-1">CSV File</label>
        <input type="file" name="file" accept=".csv,.txt" required
               class="w-full text-sm text-ink file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700">
      </div>
      <div class="flex items-center justify-between">
        <a href="/api/posts/bulk/sample-csv" class="text-xs text-brand-600 hover:underline">Download template</a>
        <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#4a8ccc">Import</button>
      </div>
    </form>
  </div>
</div>
@endsection
