{{--
  Widget: KPI Cards
  Vars: $kpi, $followerKpi, $timeSeries, $dateRange
--}}
@php
  $totalEngagement    = ($kpi['total_likes'] ?? 0) + ($kpi['total_comments'] ?? 0) + ($kpi['total_shares'] ?? 0) + ($kpi['total_saves'] ?? 0);
  $engRate            = number_format($kpi['avg_engagement_rate'] ?? 0, 1);
  $followerGrowthSign = ($followerKpi['net_growth'] ?? 0) >= 0 ? '+' : '';
  $ctr                = ($kpi['total_impressions'] ?? 0) > 0
                          ? round(($kpi['total_clicks'] ?? 0) / $kpi['total_impressions'] * 100, 2) : 0;
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

  {{-- Reach --}}
  <div class="card p-5">
    <div class="flex items-center justify-between">
      <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Reach</div>
      <span class="pill pill-mint text-xs">{{ number_format($kpi['total_reach'] ?? 0) }}</span>
    </div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['total_reach'] ?? 0) }}</div>
    <div class="text-xs text-ink/50 mt-1">{{ number_format($kpi['total_impressions'] ?? 0) }} impressions</div>
    <canvas id="wSparkReach" class="mt-4 w-full h-10"></canvas>
  </div>

  {{-- Engagement --}}
  <div class="card p-5">
    <div class="flex items-center justify-between">
      <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Engagement</div>
      <span class="pill pill-mint text-xs">{{ $engRate }}%</span>
    </div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($totalEngagement) }}</div>
    <div class="text-xs text-ink/50 mt-1">{{ $engRate }}% avg rate · {{ number_format($kpi['total_posts'] ?? 0) }} posts</div>
    <canvas id="wSparkEngagement" class="mt-4 w-full h-10"></canvas>
  </div>

  {{-- Followers --}}
  <div class="card p-5">
    <div class="flex items-center justify-between">
      <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Followers</div>
      <span class="pill text-xs {{ ($followerKpi['net_growth'] ?? 0) >= 0 ? 'pill-mint' : 'pill-coral' }}">
        {{ $followerGrowthSign }}{{ number_format($followerKpi['net_growth'] ?? 0) }}
      </span>
    </div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($followerKpi['total_followers'] ?? 0) }}</div>
    <div class="text-xs text-ink/50 mt-1">
      {{ $followerGrowthSign }}{{ number_format($followerKpi['growth_rate'] ?? 0, 1) }}% growth this period
    </div>
    <canvas id="wSparkFollowers" class="mt-4 w-full h-10"></canvas>
  </div>

  {{-- Link clicks --}}
  <div class="card p-5">
    <div class="flex items-center justify-between">
      <div class="text-xs font-bold uppercase tracking-wider text-ink/50">Link Clicks</div>
      <span class="pill text-xs {{ $ctr > 1.5 ? 'pill-mint' : ($ctr > 0 ? 'pill-gold' : '') }}">{{ $ctr }}%</span>
    </div>
    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($kpi['total_clicks'] ?? 0) }}</div>
    <div class="text-xs text-ink/50 mt-1">{{ $ctr }}% CTR average</div>
    <canvas id="wSparkClicks" class="mt-4 w-full h-10"></canvas>
  </div>

</div>
