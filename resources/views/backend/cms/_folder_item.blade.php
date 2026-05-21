<div>
  <a href="{{ route('cms.index', ['folder_id' => $folder['uuid']]) }}"
     class="flex items-center gap-1.5 py-1.5 rounded-lg text-sm transition-colors {{ request('folder_id') === $folder['uuid'] ? 'bg-brand-50 text-brand-700 font-medium' : 'text-ink/60 hover:bg-paper' }}"
     style="padding-left:{{ 8 + ($depth * 10) }}px;padding-right:8px">
    <svg class="w-3.5 h-3.5 flex-shrink-0 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
    <span class="truncate flex-1">{{ $folder['name'] }}</span>
  </a>

  @if(!empty($folder['children']))
    @foreach($folder['children'] as $child)
      @include('backend.cms._folder_item', ['folder' => $child, 'depth' => $depth + 1])
    @endforeach
  @endif
</div>
