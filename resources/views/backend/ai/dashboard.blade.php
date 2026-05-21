@extends('layouts.backend')
@section('title', 'AI Marketing Assistant')

@section('content')
<div class="flex items-center gap-2 mb-1">
  <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600">AI Powered</div>
</div>
<h1 class="text-3xl font-extrabold tracking-tight text-ink mb-1">AI Marketing Assistant</h1>
<p class="text-ink/60 text-sm mb-6">Generate captions, hashtags, ad copy, SEO content, campaigns and more.</p>

<div class="grid lg:grid-cols-[220px_1fr] gap-6">
  <div>@include('backend.ai._sidebar')</div>

  <div class="space-y-6">

    {{-- Usage bar --}}
    @php $pct = $usage['usage_percent'] ?? 0; @endphp
    <div class="card p-5">
      <div class="flex items-center justify-between mb-2">
        <div class="text-sm font-bold">Monthly AI usage</div>
        <a href="{{ route('ai.usage') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Details →</a>
      </div>
      <div class="h-2 bg-line rounded-full overflow-hidden mb-2">
        <div class="h-full rounded-full transition-all duration-700 {{ $pct >= 90 ? 'bg-coral' : ($pct >= 70 ? 'bg-gold' : 'bg-brand-400') }}"
             style="width:{{ min(100, $pct) }}%"></div>
      </div>
      <div class="flex justify-between text-xs text-ink/50">
        <span>{{ number_format($usage['current_month_usage'] ?? 0) }} tokens used</span>
        <span>{{ number_format($usage['monthly_limit'] ?? 0) }} limit</span>
      </div>
      @if($usage['is_over_limit'] ?? false)
      <div class="mt-2 text-xs font-bold text-coral">Limit reached — upgrade to continue generating.</div>
      @elseif($usage['is_near_limit'] ?? false)
      <div class="mt-2 text-xs font-semibold text-gold">Approaching monthly limit ({{ number_format($usage['remaining_tokens'] ?? 0) }} tokens left).</div>
      @endif
    </div>

    {{-- Tool grid --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
      @php
        $tools = [
          ['route' => 'ai.caption',       'color' => 'brand',  'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3l-4 4z', 'title' => 'Caption Generator', 'desc' => 'Write viral captions for any platform & tone'],
          ['route' => 'ai.hashtag',       'color' => 'mint',   'icon' => 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14', 'title' => 'Hashtag Generator', 'desc' => 'Find trending & niche hashtags that drive reach'],
          ['route' => 'ai.content-ideas', 'color' => 'gold',   'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707', 'title' => 'Content Ideas', 'desc' => 'Get fresh post ideas, hooks, and content calendars'],
          ['route' => 'ai.seo',           'color' => 'coral',  'icon' => 'M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z', 'title' => 'SEO Optimizer', 'desc' => 'Score & optimize captions and copy for search'],
          ['route' => 'ai.ad-copy',       'color' => 'brand',  'icon' => 'M11 5.882V19.24a1.76 1.76 0 0 1-3.417.592l-2.147-6.15', 'title' => 'Ad Copy', 'desc' => 'Generate high-converting ads for every platform'],
          ['route' => 'ai.responses',     'color' => 'mint',   'icon' => 'M17 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2v4l-4-4H9', 'title' => 'Response Suggestions', 'desc' => 'Draft smart replies to comments & DMs'],
          ['route' => 'ai.campaign',      'color' => 'gold',   'icon' => 'M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6', 'title' => 'Campaign Builder', 'desc' => 'Full campaign strategy with content calendar'],
          ['route' => 'ai.chat',          'color' => 'coral',  'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8', 'title' => 'AI Chat', 'desc' => 'Chat with your AI marketing co-pilot'],
        ];
        $colorMap = ['brand' => 'bg-brand-50 text-brand-600', 'mint' => 'bg-green-50 text-green-600', 'gold' => 'bg-amber-50 text-amber-600', 'coral' => 'bg-red-50 text-red-500'];
      @endphp
      @foreach($tools as $tool)
      <a href="{{ route($tool['route']) }}"
        class="card p-5 hover:border-brand-200 hover:shadow-md transition-all group flex flex-col gap-3">
        <div class="w-10 h-10 rounded-xl {{ $colorMap[$tool['color']] }} flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="{{ $tool['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div>
          <div class="font-bold text-ink group-hover:text-brand-700 transition-colors">{{ $tool['title'] }}</div>
          <div class="text-xs text-ink/50 mt-0.5">{{ $tool['desc'] }}</div>
        </div>
        <div class="mt-auto text-xs font-bold text-brand-600 group-hover:text-brand-700">Open →</div>
      </a>
      @endforeach
    </div>

    {{-- Recent content --}}
    @if($recent->count() > 0)
    <div class="card p-6">
      <h3 class="text-lg font-bold mb-4">Recent generations</h3>
      <div class="space-y-2">
        @foreach($recent as $item)
        <div class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-paper transition-colors">
          <span class="pill text-xs capitalize">{{ str_replace('_', ' ', $item->content_type) }}</span>
          <span class="flex-1 text-sm text-ink/80 truncate">{{ $item->title ?: 'Untitled' }}</span>
          <span class="text-xs text-ink/40">{{ $item->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Configured providers --}}
    <div class="card p-5">
      <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">Configured AI Providers</div>
      <div class="flex items-center gap-3">
        @foreach(['openai' => 'OpenAI GPT', 'claude' => 'Claude AI', 'gemini' => 'Gemini'] as $key => $label)
        <div class="flex items-center gap-1.5 text-sm">
          <span class="w-2 h-2 rounded-full {{ in_array($key, $configured) ? 'bg-mint' : 'bg-line' }}"></span>
          <span class="{{ in_array($key, $configured) ? 'font-semibold text-ink' : 'text-ink/40' }}">{{ $label }}</span>
        </div>
        @endforeach
      </div>
      @if(count($configured) === 0)
      <p class="text-xs text-ink/40 mt-2">Add API keys to your <code>.env</code> file to enable AI features.</p>
      @endif
    </div>

  </div>
</div>
@endsection
