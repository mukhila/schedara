<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #021b2e; }
  .header { background: #021b2e; color: #fff; padding: 28px 32px; display: flex; justify-content: space-between; align-items: center; }
  .logo { font-size: 18px; font-weight: 800; letter-spacing: -0.5px; }
  .header-meta { text-align: right; font-size: 10px; opacity: 0.6; }
  .section { padding: 24px 32px; }
  .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #4a8ccc; margin-bottom: 10px; }
  h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
  .subtitle { font-size: 12px; color: rgba(2,27,46,.5); }
  .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
  .kpi-card { background: #f5fefe; border: 1px solid #e3e9ee; border-radius: 10px; padding: 14px; }
  .kpi-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: rgba(2,27,46,.4); margin-bottom: 4px; }
  .kpi-value { font-size: 20px; font-weight: 800; }
  .divider { height: 1px; background: #e3e9ee; margin: 0 32px; }
  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  th { text-align: left; padding: 8px; background: #f5fefe; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: rgba(2,27,46,.4); border-bottom: 2px solid #e3e9ee; }
  td { padding: 8px; border-bottom: 1px solid #e3e9ee; }
  .footer { background: #f5fefe; padding: 16px 32px; font-size: 9px; color: rgba(2,27,46,.4); text-align: center; border-top: 1px solid #e3e9ee; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 9px; font-weight: 700; }
  .badge-green { background: rgba(34,176,126,.1); color: #22B07E; }
  .badge-blue  { background: rgba(101,161,216,.12); color: #4a8ccc; }
</style>
</head>
<body>

<div class="header">
  <div>
    <div class="logo">{{ $branding?->brand_name ?? 'Schedara' }}</div>
    <div style="font-size:10px;opacity:.5;margin-top:2px">Social Media Analytics Report</div>
  </div>
  <div class="header-meta">
    <div>{{ $workspace->workspace_name }}</div>
    <div>{{ $client->client_name }}</div>
    <div>Generated {{ $generated_at }}</div>
  </div>
</div>

<div class="section">
  <div class="section-title">Report Overview</div>
  <h1>{{ $workspace->workspace_name }}</h1>
  <div class="subtitle">Period: {{ $period_start }} – {{ $period_end }}</div>
</div>

<div class="divider"></div>

<div class="section">
  <div class="section-title">Key Metrics</div>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Total Posts</div>
      <div class="kpi-value">{{ number_format($metrics['total_posts']) }}</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Total Reach</div>
      <div class="kpi-value">{{ number_format($metrics['total_reach']) }}</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Engagement</div>
      <div class="kpi-value">{{ number_format($metrics['total_engagement']) }}</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">New Followers</div>
      <div class="kpi-value">+{{ number_format($metrics['followers_gained']) }}</div>
    </div>
  </div>
</div>

@if(!empty($metrics['top_posts']))
<div class="divider"></div>
<div class="section">
  <div class="section-title">Top Performing Posts</div>
  <table>
    <thead>
      <tr>
        <th>Content</th>
        <th>Platform</th>
        <th>Reach</th>
        <th>Engagement</th>
      </tr>
    </thead>
    <tbody>
      @foreach($metrics['top_posts'] as $post)
      <tr>
        <td>{{ Str::limit($post['content'] ?? '', 60) }}</td>
        <td>{{ ucfirst($post['platform'] ?? '') }}</td>
        <td>{{ number_format($post['reach'] ?? 0) }}</td>
        <td>{{ number_format($post['engagement'] ?? 0) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

<div class="footer">
  {{ $branding?->brand_name ?? 'Schedara' }} · Generated on {{ $generated_at }} · {{ $workspace->workspace_name }}
  @if(!($branding?->hide_saas_branding))
  · Powered by Schedara
  @endif
</div>

</body>
</html>
