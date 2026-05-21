@extends('layouts.backend')
@section('page_title', 'Usage & Limits')

@section('styles')
<style>
.usage-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;margin-bottom:2rem}
.usage-card{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);padding:1.5rem}
.usage-label{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(2,27,46,.4);margin-bottom:.5rem}
.usage-values{display:flex;align-items:baseline;gap:.35rem;margin-bottom:.5rem}
.usage-current{font-size:1.6rem;font-weight:800;color:#021b2e;letter-spacing:-.03em}
.usage-limit{font-size:.9rem;color:rgba(2,27,46,.35);font-weight:600}
.progress-bar{height:8px;background:rgba(2,27,46,.08);border-radius:99px;overflow:hidden}
.progress-fill{height:100%;border-radius:99px;transition:width .5s ease}
.fill-ok{background:#65a1d8}
.fill-warn{background:#f59e0b}
.fill-danger{background:#ef4444}
.usage-sub{font-size:.75rem;color:rgba(2,27,46,.4);margin-top:.4rem}
.alert-bar{padding:.875rem 1.25rem;border-radius:10px;display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;font-size:.875rem}
.alert-warn{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);color:#92400e}
.feature-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;margin-bottom:.75rem}
</style>
@endsection

@section('topbar_actions')
<a href="{{ route('billing.plans') }}" class="btn btn-primary btn-sm">
  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7" stroke-linecap="round"/></svg>
  Upgrade Plan
</a>
@endsection

@section('content')

@php
  $nearLimit = $usage->filter(fn ($u) => $u->isNearLimit());
@endphp

@if($nearLimit->isNotEmpty())
  <div class="alert-bar alert-warn">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <span>You're approaching limits on <strong>{{ $nearLimit->keys()->map(fn ($k) => str_replace('_', ' ', ucfirst($k)))->join(', ') }}</strong>. Consider upgrading.</span>
    <a href="{{ route('billing.plans') }}" style="margin-left:auto;font-weight:700;color:#d97706;text-decoration:none">Upgrade →</a>
  </div>
@endif

<div style="margin-bottom:1.25rem">
  <div style="font-size:1.05rem;font-weight:800;color:#021b2e">Usage Overview</div>
  @if($plan)
    <div style="font-size:.82rem;color:rgba(2,27,46,.45);margin-top:.2rem">
      Current plan: <strong>{{ $plan->name }}</strong>
    </div>
  @endif
</div>

@php
  $featureIcons = [
    'social_accounts'   => ['📱', 'rgba(101,161,216,.1)'],
    'scheduled_posts'   => ['📅', 'rgba(139,92,246,.1)'],
    'team_members'      => ['👥', 'rgba(34,176,126,.1)'],
    'storage_mb'        => ['💾', 'rgba(245,158,11,.1)'],
    'ai_credits'        => ['✨', 'rgba(239,68,68,.1)'],
    'analytics_reports' => ['📊', 'rgba(59,130,246,.1)'],
  ];
  $featureLabels = [
    'social_accounts'   => 'Social Accounts',
    'scheduled_posts'   => 'Scheduled Posts',
    'team_members'      => 'Team Members',
    'storage_mb'        => 'Storage',
    'ai_credits'        => 'AI Credits',
    'analytics_reports' => 'Analytics Reports',
  ];
@endphp

<div class="usage-grid">
  @foreach($usage as $feature => $track)
    @php
      $icon    = $featureIcons[$feature] ?? ['📦', 'rgba(2,27,46,.06)'];
      $label   = $featureLabels[$feature] ?? str_replace('_', ' ', ucfirst($feature));
      $pct     = $track->percentageUsed();
      $fillCls = $pct >= 90 ? 'fill-danger' : ($pct >= 75 ? 'fill-warn' : 'fill-ok');
      $unlimited = $track->isUnlimited();
      $displayValue = $feature === 'storage_mb'
        ? ($track->current_usage >= 1024 ? round($track->current_usage / 1024, 1) . ' GB' : $track->current_usage . ' MB')
        : $track->current_usage;
      $limitValue = $feature === 'storage_mb'
        ? ($track->usage_limit >= 1024 ? round($track->usage_limit / 1024, 1) . ' GB' : $track->usage_limit . ' MB')
        : $track->usage_limit;
    @endphp
    <div class="usage-card">
      <div class="feature-icon" style="background:{{ $icon[1] }}">{{ $icon[0] }}</div>
      <div class="usage-label">{{ $label }}</div>
      <div class="usage-values">
        <span class="usage-current">{{ $displayValue }}</span>
        @if(! $unlimited)
          <span class="usage-limit">/ {{ $limitValue }}</span>
        @else
          <span class="usage-limit">/ ∞</span>
        @endif
      </div>
      @if(! $unlimited)
        <div class="progress-bar">
          <div class="progress-fill {{ $fillCls }}" style="width:{{ min(100, $pct) }}%"></div>
        </div>
        <div class="usage-sub">
          @if($track->isExhausted())
            <span style="color:#dc2626;font-weight:700">Limit reached</span>
          @else
            {{ $track->remaining() !== null ? $track->remaining() . ' remaining' : '' }}
            @if($track->reset_date) · Resets {{ $track->reset_date->diffForHumans() }} @endif
          @endif
        </div>
      @else
        <div class="progress-bar">
          <div class="progress-fill fill-ok" style="width:8%"></div>
        </div>
        <div class="usage-sub">Unlimited</div>
      @endif
    </div>
  @endforeach
</div>

@if($usage->isEmpty())
  <div style="text-align:center;padding:3rem;color:rgba(2,27,46,.35)">
    No usage data yet. Subscribe to a plan to start tracking.
  </div>
@endif

@endsection
