@extends('layouts.backend')

@section('title', $post->title ?: 'Post Details')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('posts.index') }}" class="text-ink/40 hover:text-ink transition-colors">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <h1 class="text-xl font-bold text-ink flex-1">
      {{ $post->title ?: str($post->content)->limit(60) }}
    </h1>
    <div class="flex items-center gap-2">
      @if(in_array($post->status, ['draft','scheduled']))
        <a href="{{ route('posts.edit', $post->uuid) }}"
           class="px-3 py-1.5 text-sm rounded-lg border border-line hover:bg-line/40 transition-colors">Edit</a>
      @endif
      <form method="POST" action="{{ route('posts.destroy', $post->uuid) }}" onsubmit="return confirm('Delete this post?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-3 py-1.5 text-sm rounded-lg border border-coral/30 text-coral hover:bg-coral/5 transition-colors">Delete</button>
      </form>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main content --}}
    <div class="lg:col-span-2 space-y-4">

      {{-- Status banner --}}
      @php
        $statusColors = ['draft'=>'bg-line/60 text-ink/60','scheduled'=>'bg-brand-100 text-brand-700','published'=>'bg-mint/10 text-mint','failed'=>'bg-coral/10 text-coral','partial'=>'bg-gold/20 text-yellow-700','publishing'=>'bg-mint/10 text-mint'];
      @endphp
      <div class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $statusColors[$post->status] ?? '' }}">
        <span class="font-semibold capitalize">{{ $post->status }}</span>
        @if($post->scheduled_at)
          <span class="text-sm">· Scheduled for {{ $post->scheduled_at->format('M d, Y H:i') }} {{ $post->timezone }}</span>
        @endif
        @if($post->published_at)
          <span class="text-sm">· Published {{ $post->published_at->diffForHumans() }}</span>
        @endif
      </div>

      {{-- Content --}}
      <div class="bg-white rounded-2xl border border-line p-5">
        <h3 class="text-sm font-semibold text-ink/50 mb-3">Content</h3>
        <p class="text-sm text-ink whitespace-pre-wrap">{{ $post->content }}</p>
        @if($post->caption)
          <div class="mt-4 pt-4 border-t border-line">
            <p class="text-xs text-ink/50 mb-1">Caption</p>
            <p class="text-sm text-ink">{{ $post->caption }}</p>
          </div>
        @endif
      </div>

      {{-- Media --}}
      @if($post->media->isNotEmpty())
        <div class="bg-white rounded-2xl border border-line p-5">
          <h3 class="text-sm font-semibold text-ink/50 mb-3">Media ({{ $post->media->count() }})</h3>
          <div class="grid grid-cols-3 gap-3">
            @foreach($post->media as $media)
              <div class="aspect-square rounded-xl overflow-hidden bg-line/40 relative">
                @if($media->media_type === 'image')
                  <img src="{{ $media->file_url }}" alt="" class="w-full h-full object-cover">
                @elseif($media->media_type === 'video')
                  <video src="{{ $media->file_url }}" class="w-full h-full object-cover"></video>
                @endif
                <div class="absolute top-1 right-1">
                  <span class="text-xs px-1.5 py-0.5 rounded bg-ink/50 text-white">
                    {{ $media->processing_status }}
                  </span>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Platform configs --}}
      <div class="bg-white rounded-2xl border border-line p-5">
        <h3 class="text-sm font-semibold text-ink/50 mb-3">Platform Delivery</h3>
        <div class="divide-y divide-line">
          @forelse($post->platformConfigs as $config)
            @php
              $pColors = ['pending'=>'bg-line text-ink/50','published'=>'bg-mint/10 text-mint','failed'=>'bg-coral/10 text-coral'];
            @endphp
            <div class="flex items-center justify-between py-2.5">
              <div class="flex items-center gap-3">
                <span class="text-sm font-medium capitalize">{{ $config->platform }}</span>
                @if($config->socialAccount)
                  <span class="text-xs text-ink/50">{{ $config->socialAccount->account_name }}</span>
                @endif
              </div>
              <div class="flex items-center gap-3">
                @if($config->platform_post_id)
                  <span class="text-xs text-ink/40">ID: {{ $config->platform_post_id }}</span>
                @endif
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $pColors[$config->status] ?? '' }}">
                  {{ ucfirst($config->status) }}
                </span>
              </div>
            </div>
          @empty
            <p class="text-sm text-ink/40 py-3">No platforms selected.</p>
          @endforelse
        </div>
      </div>

      {{-- Activity log --}}
      @if($post->logs->isNotEmpty())
        <div class="bg-white rounded-2xl border border-line p-5">
          <h3 class="text-sm font-semibold text-ink/50 mb-3">Activity Log</h3>
          <div class="space-y-2">
            @foreach($post->logs->take(20) as $log)
              <div class="flex items-start gap-3 text-xs">
                <span class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $log->status === 'success' ? 'bg-mint' : 'bg-coral' }}"></span>
                <div class="flex-1">
                  <span class="font-medium text-ink/70 capitalize">{{ str_replace('_',' ',$log->action) }}</span>
                  @if($log->platform)
                    <span class="text-ink/40"> · {{ $log->platform }}</span>
                  @endif
                  @if($log->message)
                    <p class="text-ink/50 mt-0.5">{{ $log->message }}</p>
                  @endif
                </div>
                <span class="text-ink/30 flex-shrink-0">{{ $log->created_at->diffForHumans() }}</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">

      <div class="bg-white rounded-2xl border border-line p-5 space-y-3">
        <div>
          <p class="text-xs text-ink/40 mb-0.5">Type</p>
          <p class="text-sm font-medium capitalize">{{ $post->type }}</p>
        </div>
        <div>
          <p class="text-xs text-ink/40 mb-0.5">Platforms</p>
          <div class="flex flex-wrap gap-1 mt-1">
            @php $pBgs = ['facebook'=>'#1877F2','instagram'=>'#E1306C','twitter'=>'#1DA1F2','linkedin'=>'#0A66C2','pinterest'=>'#E60023','youtube'=>'#FF0000','threads'=>'#555']; @endphp
            @foreach($post->platforms ?? [] as $p)
              <span class="text-xs px-2 py-0.5 rounded-full text-white font-medium" style="background:{{ $pBgs[$p] ?? '#888' }}">{{ ucfirst($p) }}</span>
            @endforeach
          </div>
        </div>
        @if($post->hashtags->isNotEmpty())
          <div>
            <p class="text-xs text-ink/40 mb-1">Hashtags</p>
            <div class="flex flex-wrap gap-1">
              @foreach($post->hashtags as $tag)
                <span class="text-xs px-2 py-0.5 rounded-full bg-brand-50 text-brand-700">#{{ $tag->hashtag }}</span>
              @endforeach
            </div>
          </div>
        @endif
        @if($post->is_evergreen)
          <div>
            <span class="text-xs px-2.5 py-1 rounded-full bg-mint/10 text-mint font-medium">Evergreen</span>
            @if($post->auto_repost && $post->repost_frequency)
              <p class="text-xs text-ink/50 mt-1">Auto-repost every {{ $post->repost_frequency }} days</p>
            @endif
          </div>
        @endif
        <div>
          <p class="text-xs text-ink/40">Created {{ $post->created_at->format('M d, Y') }}</p>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
