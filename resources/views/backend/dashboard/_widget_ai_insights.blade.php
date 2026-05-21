{{--
  Widget: AI Insights & Predictions
  Vars: $aiInsights (nullable array with 'forecast' and 'viral')
--}}
@if(config('analytics.ai_analysis_enabled') && !empty($aiInsights))

@php
  $forecast    = $aiInsights['forecast'] ?? [];
  $viralPosts  = $aiInsights['viral'] ?? [];
@endphp

<div class="card p-6">
  <div class="flex items-center gap-2 mb-5">
    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 6v6l4 2"/>
        <path d="M20 2v4m2-2h-4"/>
      </svg>
    </div>
    <div>
      <h3 class="text-lg font-bold">AI Insights</h3>
      <p class="text-xs text-ink/50">Predictions &amp; recommendations powered by AI</p>
    </div>
    <span class="ml-auto pill pill-brand text-xs">AI</span>
  </div>

  <div class="grid lg:grid-cols-2 gap-4">

    {{-- Forecast --}}
    <div class="bg-paper rounded-xl p-4">
      <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">Engagement Forecast</div>
      @if(!empty($forecast))
      <div class="space-y-2">
        @foreach(array_slice((array) $forecast, 0, 5) as $point)
        <div class="flex items-center justify-between text-sm">
          <span class="text-ink/70">{{ $point['period'] ?? $point['date'] ?? '—' }}</span>
          <span class="font-bold text-ink">{{ number_format($point['predicted_engagement'] ?? $point['value'] ?? 0) }}</span>
        </div>
        @endforeach
      </div>
      @else
      <p class="text-sm text-ink/40">Not enough data for a forecast yet. Publish more posts to enable predictions.</p>
      @endif
    </div>

    {{-- Viral posts --}}
    <div class="bg-paper rounded-xl p-4">
      <div class="text-xs font-bold uppercase tracking-wider text-ink/50 mb-3">
        Viral Content Detected
        @if(!empty($viralPosts))
        <span class="ml-1 text-coral font-extrabold">{{ count($viralPosts) }}</span>
        @endif
      </div>
      @if(!empty($viralPosts))
      <div class="space-y-2">
        @foreach(array_slice((array) $viralPosts, 0, 4) as $post)
        <div class="flex items-start gap-2 text-sm">
          <svg class="w-4 h-4 text-coral flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 3.9 2.4-7.4L2 9.4h7.6z"/></svg>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-ink truncate">{{ Str::limit($post['caption'] ?? 'Post', 40) }}</div>
            <div class="text-xs text-ink/40 capitalize">{{ $post['platform'] ?? '' }}</div>
          </div>
          <span class="pill pill-coral text-xs flex-shrink-0">{{ number_format($post['engagement_rate'] ?? 0, 1) }}%</span>
        </div>
        @endforeach
      </div>
      @else
      <p class="text-sm text-ink/40">No viral posts detected in this period. Keep publishing consistently!</p>
      @endif
    </div>

  </div>
</div>

@else
{{-- AI disabled --}}
<div class="card p-6 border-dashed">
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl bg-paper flex items-center justify-center flex-shrink-0">
      <svg class="w-5 h-5 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 6v6l4 2"/><path d="M20 2v4m2-2h-4"/>
      </svg>
    </div>
    <div>
      <h3 class="text-base font-bold text-ink/60">AI Insights</h3>
      <p class="text-xs text-ink/40 mt-0.5">Enable AI analysis in config to unlock engagement forecasts and viral post detection.</p>
    </div>
  </div>
</div>
@endif
