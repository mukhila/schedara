{{--
  Widget: Follower Growth
  Vars: $followerKpi, $byPlatform, $timeSeries
--}}
@php
  $platformColors = [
    'instagram' => '#65a1d8', 'facebook' => '#021b2e',
    'twitter'   => '#2f76bd', 'linkedin' => '#8bb4dc',
    'tiktok'    => '#021b2e', 'youtube'  => '#FF0000',
    'threads'   => '#555',
  ];
  $followerGrowthSign = ($followerKpi['net_growth'] ?? 0) >= 0 ? '+' : '';
@endphp

<div class="card p-6">
  <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
    <div>
      <h3 class="text-lg font-bold">Follower Growth</h3>
      <p class="text-xs text-ink/50 mt-1">
        {{ $followerGrowthSign }}{{ number_format($followerKpi['net_growth'] ?? 0) }} net this period ·
        {{ number_format($followerKpi['total_followers'] ?? 0) }} total
      </p>
    </div>
    <div class="flex items-center gap-4 text-sm">
      <div class="text-center">
        <div class="text-xs text-ink/50 font-bold uppercase tracking-wider">New</div>
        <div class="text-lg font-extrabold text-mint">+{{ number_format($followerKpi['new_followers'] ?? 0) }}</div>
      </div>
      <div class="text-center">
        <div class="text-xs text-ink/50 font-bold uppercase tracking-wider">Lost</div>
        <div class="text-lg font-extrabold text-coral">-{{ number_format($followerKpi['unfollows'] ?? 0) }}</div>
      </div>
      <div class="text-center">
        <div class="text-xs text-ink/50 font-bold uppercase tracking-wider">Growth</div>
        <div class="text-lg font-extrabold {{ ($followerKpi['growth_rate'] ?? 0) >= 0 ? 'text-mint' : 'text-coral' }}">
          {{ number_format($followerKpi['growth_rate'] ?? 0, 1) }}%
        </div>
      </div>
    </div>
  </div>

  @if(count($byPlatform) > 0)
  <div class="space-y-3 mb-5">
    @php $totalFollowers = max(array_sum(array_column($byPlatform, 'reach')), 1); @endphp
    @foreach(array_slice($byPlatform, 0, 5) as $p)
    @php
      $color = $platformColors[strtolower($p['platform'])] ?? '#65a1d8';
      $pct   = $totalFollowers > 0 ? min(100, round($p['reach'] / $totalFollowers * 100, 1)) : 0;
    @endphp
    <div>
      <div class="flex justify-between text-xs mb-1">
        <span class="font-semibold text-ink/80 capitalize">{{ $p['platform'] }}</span>
        <span class="font-bold">{{ $pct }}%</span>
      </div>
      <div class="h-1.5 bg-line rounded-full overflow-hidden">
        <div class="h-full rounded-full transition-all duration-500" style="width:{{ $pct }}%;background:{{ $color }}"></div>
      </div>
    </div>
    @endforeach
  </div>
  @else
  <div class="flex flex-col items-center justify-center h-24 text-ink/30 gap-2 mb-5">
    <p class="text-sm">Connect social accounts to see follower breakdown</p>
  </div>
  @endif

  <div class="pt-4 border-t border-line">
    <a href="{{ route('analytics.followers') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 transition-colors flex items-center gap-1">
      View detailed follower analytics →
    </a>
  </div>
</div>
