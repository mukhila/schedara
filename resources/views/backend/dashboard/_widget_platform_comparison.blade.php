{{--
  Widget: Platform Comparison
  Vars: $byPlatform, $dateRange
--}}
@php
  $platformColors = [
    'instagram' => '#65a1d8', 'facebook' => '#021b2e',
    'twitter'   => '#2f76bd', 'linkedin' => '#8bb4dc',
    'tiktok'    => '#021b2e', 'youtube'  => '#FF0000',
    'threads'   => '#555',
  ];
  $totalEngagement = array_sum(array_column($byPlatform, 'engagement')) ?: 1;
@endphp

<div class="grid lg:grid-cols-[1.7fr_1fr] gap-4">

  {{-- Platform table --}}
  <div class="card p-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-lg font-bold">Platform breakdown</h3>
        <p class="text-xs text-ink/50 mt-1">{{ $dateRange['from'] ?? '' }} – {{ $dateRange['to'] ?? '' }}</p>
      </div>
      <a href="{{ route('analytics.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 transition-colors">Full analytics →</a>
    </div>
    @if(count($byPlatform) > 0)
    <div class="overflow-x-auto -mx-2">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-[10px] font-bold uppercase tracking-wider text-ink/40 text-left">
            <th class="px-2 py-2">Platform</th>
            <th class="px-2 py-2 text-right">Posts</th>
            <th class="px-2 py-2 text-right">Reach</th>
            <th class="px-2 py-2 text-right">Engagement</th>
            <th class="px-2 py-2 text-right">Eng. Rate</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @foreach($byPlatform as $p)
          <tr class="hover:bg-paper/80 transition-colors">
            <td class="px-2 py-3">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:{{ $platformColors[strtolower($p['platform'])] ?? '#aaa' }}"></span>
                <span class="font-bold text-ink capitalize">{{ $p['platform'] }}</span>
              </div>
            </td>
            <td class="px-2 py-3 text-right font-semibold">{{ number_format($p['posts']) }}</td>
            <td class="px-2 py-3 text-right font-semibold">{{ number_format($p['reach']) }}</td>
            <td class="px-2 py-3 text-right font-semibold">{{ number_format($p['engagement']) }}</td>
            <td class="px-2 py-3 text-right">
              <span class="pill text-xs {{ $p['engagement_rate'] >= 5 ? 'pill-mint' : ($p['engagement_rate'] >= 2 ? 'pill-gold' : 'pill-coral') }}">
                {{ number_format($p['engagement_rate'], 2) }}%
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="flex flex-col items-center justify-center h-32 text-ink/40 gap-2">
      <p class="text-sm">Connect social accounts to see platform data</p>
    </div>
    @endif
  </div>

  {{-- Channel donut --}}
  <div class="card p-6">
    <h3 class="text-lg font-bold">By channel</h3>
    <p class="text-xs text-ink/50 mt-1 mb-5">Share of engagement</p>
    @if(count($byPlatform) > 0)
    <div class="flex items-center gap-6">
      <div class="relative w-36 h-36 shrink-0">
        <canvas id="wDonutChart" width="144" height="144"></canvas>
        <div class="absolute inset-0 grid place-items-center pointer-events-none">
          <div class="text-center">
            <div class="text-[10px] uppercase tracking-wider font-bold text-ink/50">Total</div>
            <div class="text-xl font-extrabold leading-none">
              {{ number_format(array_sum(array_column($byPlatform, 'engagement')) / 1000, 1) }}k
            </div>
          </div>
        </div>
      </div>
      <div class="flex-1 space-y-2.5">
        @foreach($byPlatform as $p)
        @php
          $pct = $totalEngagement > 0 ? round($p['engagement'] / $totalEngagement * 100, 1) : 0;
        @endphp
        <div class="flex items-center gap-2.5 text-sm">
          <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:{{ $platformColors[strtolower($p['platform'])] ?? '#aaa' }}"></span>
          <span class="flex-1 text-ink/80 capitalize">{{ $p['platform'] }}</span>
          <span class="font-bold">{{ $pct }}%</span>
        </div>
        @endforeach
      </div>
    </div>
    @else
    <div class="flex flex-col items-center justify-center h-32 text-ink/40 gap-2">
      <p class="text-sm">No channel data yet</p>
    </div>
    @endif
  </div>

</div>
