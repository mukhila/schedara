{{-- AI Assistant sub-navigation --}}
@php
  $aiLinks = [
    ['route' => 'ai.dashboard',   'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636-.707.707M21 12h-1M4 12H3m3.343-5.657-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'label' => 'AI Dashboard'],
    ['route' => 'ai.caption',     'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3l-4 4z', 'label' => 'Caption Generator'],
    ['route' => 'ai.hashtag',     'icon' => 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14', 'label' => 'Hashtag Generator'],
    ['route' => 'ai.content-ideas','icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636-.707.707M21 12h-1M4 12H3', 'label' => 'Content Ideas'],
    ['route' => 'ai.seo',         'icon' => 'M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z', 'label' => 'SEO Optimizer'],
    ['route' => 'ai.ad-copy',     'icon' => 'M11 5.882V19.24a1.76 1.76 0 0 1-3.417.592l-2.147-6.15M18 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-7-3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z', 'label' => 'Ad Copy'],
    ['route' => 'ai.responses',   'icon' => 'M17 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2v4l-4-4H9a1.994 1.994 0 0 1-1.414-.586m0 0L11 14h4a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2v4l.586-.586z', 'label' => 'Response Suggestions'],
    ['route' => 'ai.campaign',    'icon' => 'M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 0-2 2h-2a2 2 0 0 0-2-2z', 'label' => 'Campaign Builder'],
    ['route' => 'ai.chat',        'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'AI Chat'],
    ['route' => 'ai.templates',   'icon' => 'M4 6h16M4 12h16M4 18h7', 'label' => 'Prompt Templates'],
    ['route' => 'ai.brand-voice', 'icon' => 'M19 11a7 7 0 0 1-7 7m0 0a7 7 0 0 1-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3 3 3 0 0 1 3 3v6a3 3 0 0 1-3 3z', 'label' => 'Brand Voice'],
    ['route' => 'ai.usage',       'icon' => 'M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10', 'label' => 'Usage & Limits'],
  ];
@endphp

<div class="card p-3 mb-4">
  <div class="text-[10px] font-bold uppercase tracking-widest text-ink/40 px-2 mb-2">AI Tools</div>
  <nav class="space-y-0.5">
    @foreach($aiLinks as $link)
    <a href="{{ route($link['route']) }}"
      class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm font-semibold transition-colors
        {{ request()->routeIs($link['route']) ? 'bg-brand-50 text-brand-700' : 'text-ink/70 hover:bg-paper hover:text-ink' }}">
      <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="{{ $link['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      {{ $link['label'] }}
    </a>
    @endforeach
  </nav>
</div>
