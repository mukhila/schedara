@extends('layouts.backend')

@section('title', 'Drafts')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-ink">Drafts</h1>
      <p class="text-sm text-ink/50 mt-0.5">{{ $posts->total() }} saved drafts</p>
    </div>
    <a href="{{ route('posts.create') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-opacity hover:opacity-90" style="background:#4a8ccc">
      New Post
    </a>
  </div>

  @if($posts->isEmpty())
    <div class="text-center py-20 border border-line border-dashed rounded-2xl">
      <p class="text-sm text-ink/40 mb-4">No drafts yet.</p>
      <a href="{{ route('posts.create') }}" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#4a8ccc">Create a draft</a>
    </div>
  @else
    <div class="space-y-3">
      @foreach($posts as $post)
        <div class="bg-white rounded-2xl border border-line p-4 flex items-start gap-4">
          <div class="flex-1 min-w-0">
            <a href="{{ route('posts.show', $post->uuid) }}" class="font-medium text-ink hover:text-brand-600 transition-colors">
              {{ $post->title ?: str($post->content)->limit(80) }}
            </a>
            <div class="flex items-center gap-3 mt-1.5">
              <span class="text-xs text-ink/40">{{ $post->created_at->diffForHumans() }}</span>
              @foreach($post->platforms ?? [] as $p)
                <span class="text-xs text-ink/40">{{ ucfirst($p) }}</span>
              @endforeach
            </div>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('posts.edit', $post->uuid) }}" class="text-xs px-3 py-1.5 rounded-lg border border-line hover:bg-line/40 transition-colors">Edit</a>
            <form method="POST" action="{{ route('posts.destroy', $post->uuid) }}" onsubmit="return confirm('Delete?')">
              @csrf @method('DELETE')
              <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-coral/30 text-coral hover:bg-coral/5 transition-colors">Delete</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-4">{{ $posts->withQueryString()->links() }}</div>
  @endif
</div>
@endsection
