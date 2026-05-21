@extends('layouts.backend')
@section('page_title', 'Activity Log')

@section('styles')
<style>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem}
.page-title{font-size:1.25rem;font-weight:900;color:#021b2e}
.filters-card{background:#fff;border-radius:12px;border:1px solid rgba(2,27,46,.08);padding:1rem 1.25rem;margin-bottom:1.25rem}
.filters-row{display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap}
.filter-group{display:flex;flex-direction:column;gap:.3rem}
.filter-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:rgba(2,27,46,.45)}
.filter-control{padding:.4rem .65rem;border:1px solid rgba(2,27,46,.15);border-radius:8px;font-family:inherit;font-size:.82rem;color:#021b2e;background:#fff;cursor:pointer;min-width:140px}
.filter-control:focus{border-color:#65a1d8;outline:none}
.btn-filter{background:#65a1d8;color:#fff;font-weight:700;padding:.45rem .9rem;border-radius:8px;border:none;cursor:pointer;font-family:inherit;font-size:.82rem}
.btn-reset{background:transparent;color:rgba(2,27,46,.45);font-weight:600;padding:.45rem .9rem;border-radius:8px;border:1px solid rgba(2,27,46,.12);cursor:pointer;font-family:inherit;font-size:.82rem;text-decoration:none}
.timeline{position:relative;padding-left:1.75rem}
.timeline::before{content:'';position:absolute;left:.625rem;top:0;bottom:0;width:2px;background:rgba(2,27,46,.07)}
.timeline-group{margin-bottom:1.5rem}
.date-divider{font-size:.75rem;font-weight:700;color:rgba(2,27,46,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;padding-left:0;position:relative;left:-1.75rem;padding-left:1.75rem}
.log-item{display:flex;gap:.875rem;margin-bottom:.75rem;position:relative}
.log-dot{width:12px;height:12px;border-radius:50%;position:absolute;left:-1.875rem;top:.35rem;flex-shrink:0;border:2px solid #fff}
.log-card{background:#fff;border:1px solid rgba(2,27,46,.07);border-radius:10px;padding:.65rem .875rem;flex:1;display:flex;align-items:flex-start;gap:.75rem;flex-wrap:wrap}
.log-module{font-size:.68rem;font-weight:800;padding:.15rem .45rem;border-radius:4px;text-transform:uppercase;letter-spacing:.05em;flex-shrink:0;margin-top:.1rem}
.log-body{flex:1;min-width:0}
.log-desc{font-size:.85rem;color:#021b2e;font-weight:500}
.log-meta{font-size:.75rem;color:rgba(2,27,46,.4);margin-top:.2rem;display:flex;gap:.75rem;flex-wrap:wrap}
.empty-state{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.07);padding:3rem;text-align:center;color:rgba(2,27,46,.4)}
.pagination-bar{margin-top:1.5rem}
</style>
@endsection

@section('content')

<div class="page-header">
  <div>
    <div class="page-title">Activity Log</div>
  </div>
  <a href="{{ route('collaboration.dashboard') }}" style="font-size:.82rem;color:rgba(2,27,46,.45);text-decoration:none">← Collaboration</a>
</div>

{{-- Filters --}}
<div class="filters-card">
  <form method="GET" class="filters-row">
    <div class="filter-group">
      <span class="filter-label">Team Member</span>
      <select name="user_id" class="filter-control">
        <option value="">All members</option>
        @foreach($members as $m)
          <option value="{{ $m->user_id }}" {{ ($filters['user_id'] ?? '') == $m->user_id ? 'selected' : '' }}>
            {{ $m->user->name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Module</span>
      <select name="module" class="filter-control">
        <option value="">All modules</option>
        @foreach($modules as $mod)
          <option value="{{ $mod }}" {{ ($filters['module'] ?? '') === $mod ? 'selected' : '' }}>
            {{ ucfirst($mod) }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">From</span>
      <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="filter-control">
    </div>
    <div class="filter-group">
      <span class="filter-label">To</span>
      <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="filter-control">
    </div>
    <div class="filter-group" style="flex-direction:row;gap:.5rem;align-items:center">
      <button type="submit" class="btn-filter">Apply</button>
      <a href="{{ route('collaboration.activity') }}" class="btn-reset">Reset</a>
    </div>
  </form>
</div>

{{-- Timeline --}}
@php
  $moduleColors = [
    'tasks'     => '#3b82f6',
    'approvals' => '#f59e0b',
    'comments'  => '#10b981',
    'posts'     => '#8b5cf6',
    'team'      => '#65a1d8',
    'auth'      => '#6b7280',
    'media'     => '#ec4899',
    'billing'   => '#14b8a6',
  ];

  $grouped = $logs->getCollection()->groupBy(fn ($l) => $l->created_at->toDateString());
@endphp

@if($logs->isEmpty())
  <div class="empty-state">
    <div style="font-size:2.5rem;margin-bottom:.75rem">📋</div>
    <div style="font-size:.95rem;font-weight:700;margin-bottom:.35rem">No activity recorded</div>
    <div style="font-size:.82rem">Team actions will appear here as they happen.</div>
  </div>
@else
  <div class="timeline">
    @foreach($grouped as $date => $items)
      <div class="timeline-group">
        <div class="date-divider">{{ \Carbon\Carbon::parse($date)->isToday() ? 'Today' : (\Carbon\Carbon::parse($date)->isYesterday() ? 'Yesterday' : \Carbon\Carbon::parse($date)->format('M j, Y')) }}</div>

        @foreach($items as $log)
          @php
            $mc = $moduleColors[$log->module] ?? '#6b7280';
          @endphp
          <div class="log-item">
            <div class="log-dot" style="background:{{ $mc }}"></div>
            <div class="log-card">
              <span class="log-module" style="background:{{ $mc }}18;color:{{ $mc }}">{{ $log->module }}</span>
              <div class="log-body">
                <div class="log-desc">{{ $log->description ?? $log->action }}</div>
                <div class="log-meta">
                  @if($log->user)
                    <span>{{ $log->user->name }}</span>
                  @endif
                  <span>{{ $log->created_at->format('H:i') }}</span>
                  @if($log->ip_address)
                    <span>{{ $log->ip_address }}</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>

  <div class="pagination-bar">
    {{ $logs->withQueryString()->links() }}
  </div>
@endif

@endsection
