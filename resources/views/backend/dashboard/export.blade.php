<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard Export · {{ config('app.name') }}</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body   { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 13px; color: #021b2e; background: #fff; padding: 32px 40px; }
  h1     { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }
  h2     { font-size: 15px; font-weight: 700; margin-bottom: 12px; color: #021b2e; border-bottom: 2px solid #e3e9ee; padding-bottom: 6px; }
  h3     { font-size: 13px; font-weight: 700; }
  .meta  { color: #021b2e99; font-size: 12px; margin-top: 4px; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid #021b2e; }
  .logo  { font-size: 18px; font-weight: 900; color: #65a1d8; }
  section { margin-bottom: 28px; }
  .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
  .kpi-card { border: 1px solid #e3e9ee; border-radius: 8px; padding: 14px 16px; }
  .kpi-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #021b2e66; }
  .kpi-value { font-size: 22px; font-weight: 800; margin-top: 4px; }
  .kpi-sub   { font-size: 11px; color: #021b2e66; margin-top: 2px; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  th    { text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #021b2e66; padding: 6px 8px; border-bottom: 2px solid #e3e9ee; }
  td    { padding: 8px; border-bottom: 1px solid #f0f4f7; }
  tr:last-child td { border-bottom: none; }
  .pill { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; }
  .pill-mint  { background: #d1fae5; color: #065f46; }
  .pill-coral { background: #fee2e2; color: #991b1b; }
  .pill-gold  { background: #fef9c3; color: #713f12; }
  .pill-grey  { background: #f1f5f9; color: #475569; }
  .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  .stat-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f0f4f7; }
  .stat-label { color: #021b2e99; }
  .stat-value { font-weight: 700; }
  .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e3e9ee; color: #021b2e66; font-size: 11px; display: flex; justify-content: space-between; }
  @media print {
    body { padding: 20px; }
    @page { margin: 15mm; size: A4; }
  }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
  <div>
    <div class="logo">{{ config('app.name') }}</div>
    <h1>Analytics Dashboard</h1>
    <p class="meta">
      {{ $dateRange['from'] ?? '' }} – {{ $dateRange['to'] ?? '' }} ·
      {{ $dateRange['days'] ?? 30 }} days ·
      Exported {{ now()->format('M j, Y H:i') }}
    </p>
  </div>
  <div style="text-align:right">
    <p class="meta" style="font-weight:700">{{ auth()->user()->name }}</p>
    <p class="meta">{{ auth()->user()->email }}</p>
  </div>
</div>

{{-- KPI Summary --}}
@php
  $totalEngagement    = ($kpi['total_likes'] ?? 0) + ($kpi['total_comments'] ?? 0) + ($kpi['total_shares'] ?? 0) + ($kpi['total_saves'] ?? 0);
  $engRate            = number_format($kpi['avg_engagement_rate'] ?? 0, 1);
  $followerGrowthSign = ($followerKpi['net_growth'] ?? 0) >= 0 ? '+' : '';
  $ctr                = ($kpi['total_impressions'] ?? 0) > 0
                          ? round(($kpi['total_clicks'] ?? 0) / $kpi['total_impressions'] * 100, 2) : 0;
@endphp
<section>
  <h2>Key Performance Indicators</h2>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Reach</div>
      <div class="kpi-value">{{ number_format($kpi['total_reach'] ?? 0) }}</div>
      <div class="kpi-sub">{{ number_format($kpi['total_impressions'] ?? 0) }} impressions</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Engagement</div>
      <div class="kpi-value">{{ number_format($totalEngagement) }}</div>
      <div class="kpi-sub">{{ $engRate }}% avg rate · {{ number_format($kpi['total_posts'] ?? 0) }} posts</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Followers</div>
      <div class="kpi-value">{{ number_format($followerKpi['total_followers'] ?? 0) }}</div>
      <div class="kpi-sub">{{ $followerGrowthSign }}{{ number_format($followerKpi['net_growth'] ?? 0) }} this period</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Link Clicks</div>
      <div class="kpi-value">{{ number_format($kpi['total_clicks'] ?? 0) }}</div>
      <div class="kpi-sub">{{ $ctr }}% CTR</div>
    </div>
  </div>
</section>

{{-- Revenue & ROI --}}
@php
  $roi     = $roiSummary['roi'] ?? ($campaigns['avg_roi'] ?? 0);
  $revenue = $roiSummary['total_revenue'] ?? ($campaigns['total_revenue'] ?? 0);
  $spend   = $roiSummary['total_spend']   ?? ($campaigns['total_spend'] ?? 0);
  $profit  = $roiSummary['net_profit']    ?? ($revenue - $spend);
  $roas    = $roiSummary['roas']          ?? ($spend > 0 ? round($revenue / $spend, 2) : 0);
  $cpa     = $roiSummary['cpa']           ?? 0;
  $convs   = $roiSummary['total_conversions'] ?? ($campaigns['total_conversions'] ?? 0);
@endphp
<section>
  <h2>Revenue &amp; ROI</h2>
  <div class="two-col">
    <div>
      <div class="stat-row"><span class="stat-label">Total Revenue</span><span class="stat-value">${{ number_format($revenue, 2) }}</span></div>
      <div class="stat-row"><span class="stat-label">Total Spend</span><span class="stat-value">${{ number_format($spend, 2) }}</span></div>
      <div class="stat-row"><span class="stat-label">Net Profit</span><span class="stat-value">${{ number_format($profit, 2) }}</span></div>
    </div>
    <div>
      <div class="stat-row"><span class="stat-label">ROI</span><span class="stat-value">{{ number_format($roi, 1) }}%</span></div>
      <div class="stat-row"><span class="stat-label">ROAS</span><span class="stat-value">{{ number_format($roas, 2) }}x</span></div>
      <div class="stat-row"><span class="stat-label">Conversions</span><span class="stat-value">{{ number_format($convs) }}</span></div>
    </div>
  </div>
</section>

{{-- Platform Breakdown --}}
@if(count($byPlatform) > 0)
<section>
  <h2>Platform Breakdown</h2>
  <table>
    <thead>
      <tr>
        <th>Platform</th>
        <th style="text-align:right">Posts</th>
        <th style="text-align:right">Reach</th>
        <th style="text-align:right">Impressions</th>
        <th style="text-align:right">Engagement</th>
        <th style="text-align:right">Clicks</th>
        <th style="text-align:right">Eng. Rate</th>
      </tr>
    </thead>
    <tbody>
      @foreach($byPlatform as $p)
      @php $er = $p['engagement_rate'] ?? 0; @endphp
      <tr>
        <td style="font-weight:700;text-transform:capitalize">{{ $p['platform'] }}</td>
        <td style="text-align:right">{{ number_format($p['posts']) }}</td>
        <td style="text-align:right">{{ number_format($p['reach']) }}</td>
        <td style="text-align:right">{{ number_format($p['impressions']) }}</td>
        <td style="text-align:right">{{ number_format($p['engagement']) }}</td>
        <td style="text-align:right">{{ number_format($p['clicks']) }}</td>
        <td style="text-align:right">
          <span class="pill {{ $er >= 5 ? 'pill-mint' : ($er >= 2 ? 'pill-gold' : 'pill-coral') }}">
            {{ number_format($er, 2) }}%
          </span>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</section>
@endif

{{-- Top Posts --}}
@if(!empty($postPerf) && count($postPerf) > 0)
<section>
  <h2>Top Performing Posts</h2>
  <table>
    <thead>
      <tr>
        <th>Caption</th>
        <th style="text-align:right">Platform</th>
        <th style="text-align:right">Reach</th>
        <th style="text-align:right">Engagement</th>
        <th style="text-align:right">Eng. Rate</th>
      </tr>
    </thead>
    <tbody>
      @foreach(array_slice((array) $postPerf, 0, 10) as $post)
      @php $er = $post['engagement_rate'] ?? 0; @endphp
      <tr>
        <td>{{ Str::limit($post['caption'] ?? 'Untitled', 60) }}</td>
        <td style="text-align:right;text-transform:capitalize">{{ $post['platform'] ?? '—' }}</td>
        <td style="text-align:right">{{ number_format($post['reach'] ?? 0) }}</td>
        <td style="text-align:right">{{ number_format($post['engagement_count'] ?? 0) }}</td>
        <td style="text-align:right">
          <span class="pill {{ $er >= 5 ? 'pill-mint' : ($er >= 2 ? 'pill-gold' : 'pill-coral') }}">
            {{ number_format($er, 2) }}%
          </span>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</section>
@endif

<div class="footer">
  <span>{{ config('app.name') }} · Analytics Export</span>
  <span>Generated {{ now()->toDateTimeString() }} UTC</span>
</div>

</body>
</html>
