@extends('layouts.backend')
@section('page_title', 'Collaboration')

@section('styles')
<style>
.collab-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem}
.stat-card{background:#fff;border:1px solid rgba(2,27,46,.08);border-radius:14px;padding:1.25rem;display:flex;flex-direction:column;gap:.5rem}
.stat-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:rgba(2,27,46,.45)}
.stat-value{font-size:2rem;font-weight:900;color:#021b2e;line-height:1}
.stat-sub{font-size:.78rem;color:rgba(2,27,46,.45)}
.section-card{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);overflow:hidden;margin-bottom:1.5rem}
.section-header{padding:1rem 1.25rem;border-bottom:1px solid rgba(2,27,46,.07);display:flex;align-items:center;justify-content:space-between}
.section-title{font-size:.9rem;font-weight:800;color:#021b2e}
.see-all{font-size:.78rem;font-weight:600;color:#65a1d8;text-decoration:none}
.see-all:hover{text-decoration:underline}
.task-row{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid rgba(2,27,46,.05)}
.task-row:last-child{border-bottom:0}
.priority-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.task-title{font-size:.875rem;font-weight:600;color:#021b2e;flex:1}
.task-meta{font-size:.75rem;color:rgba(2,27,46,.4)}
.due-overdue{color:#dc2626}
.badge{font-size:.7rem;font-weight:700;padding:.15rem .45rem;border-radius:5px;white-space:nowrap}
.approval-row{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid rgba(2,27,46,.05)}
.approval-row:last-child{border-bottom:0}
.approval-title{font-size:.875rem;font-weight:600;color:#021b2e;flex:1}
.btn-xs{padding:.25rem .6rem;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer;border:none;font-family:inherit}
.btn-approve{background:rgba(16,185,129,.1);color:#059669}
.btn-approve:hover{background:rgba(16,185,129,.2)}
.btn-reject{background:rgba(239,68,68,.1);color:#dc2626}
.btn-reject:hover{background:rgba(239,68,68,.2)}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.25rem;font-weight:900;color:#021b2e}
.page-subtitle{font-size:.82rem;color:rgba(2,27,46,.45);margin-top:.2rem}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem}
.activity-item{display:flex;gap:.75rem;padding:.625rem 1.25rem;border-bottom:1px solid rgba(2,27,46,.04);align-items:flex-start}
.activity-item:last-child{border-bottom:0}
.activity-dot{width:7px;height:7px;border-radius:50%;background:#65a1d8;margin-top:.35rem;flex-shrink:0}
.activity-body{flex:1}
.activity-desc{font-size:.82rem;color:#021b2e}
.activity-time{font-size:.72rem;color:rgba(2,27,46,.4);margin-top:.15rem}
.module-chip{display:inline-block;padding:.1rem .4rem;border-radius:4px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-right:.35rem}
.empty-state{padding:2rem;text-align:center;color:rgba(2,27,46,.35);font-size:.875rem}
@media(max-width:900px){.two-col{grid-template-columns:1fr}}
</style>
@endsection

@section('content')

<div class="page-header">
  <div>
    <div class="page-title">Team Collaboration</div>
    <div class="page-subtitle">Manage tasks, approvals, and team activity</div>
  </div>
  <div style="display:flex;gap:.75rem">
    <a href="{{ route('collaboration.tasks.index') }}" style="background:#65a1d8;color:#fff;font-weight:700;padding:.55rem 1.1rem;border-radius:9px;text-decoration:none;font-size:.875rem">
      Task Board
    </a>
    <a href="{{ route('collaboration.approvals.index') }}" style="background:#fff;color:#021b2e;font-weight:700;padding:.55rem 1.1rem;border-radius:9px;text-decoration:none;font-size:.875rem;border:1px solid rgba(2,27,46,.15)">
      Approvals @if($approvalStats['pending'] > 0)<span style="background:#ef4444;color:#fff;border-radius:10px;font-size:.65rem;padding:.1rem .35rem;margin-left:.35rem">{{ $approvalStats['pending'] }}</span>@endif
    </a>
  </div>
</div>

{{-- Stats row --}}
<div class="collab-grid">
  <div class="stat-card">
    <div class="stat-label">Team Members</div>
    <div class="stat-value">{{ $memberCount }}</div>
    <div class="stat-sub"><a href="{{ route('team.index') }}" style="color:#65a1d8;text-decoration:none">Manage team →</a></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">My Open Tasks</div>
    <div class="stat-value" style="color:#3b82f6">{{ $taskStats['pending'] + $taskStats['in_progress'] }}</div>
    <div class="stat-sub">{{ $taskStats['overdue'] }} overdue</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pending Approvals</div>
    <div class="stat-value" style="color:#f59e0b">{{ $approvalStats['pending'] }}</div>
    <div class="stat-sub">{{ $approvalStats['approved'] }} approved this period</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Tasks Completed</div>
    <div class="stat-value" style="color:#10b981">{{ $taskStats['completed'] }}</div>
    <div class="stat-sub">{{ $taskStats['total'] }} total</div>
  </div>
</div>

<div class="two-col">

  {{-- My Tasks --}}
  <div class="section-card">
    <div class="section-header">
      <span class="section-title">My Tasks</span>
      <a href="{{ route('collaboration.tasks.index', ['assigned_to' => auth()->id()]) }}" class="see-all">See all</a>
    </div>
    @forelse($myTasks as $task)
      @php
        $prio = $task->priorityEnum();
        $overdue = $task->isOverdue();
      @endphp
      <div class="task-row">
        <div class="priority-dot" style="background:{{ $prio->color() }}"></div>
        <div class="task-title">
          <a href="{{ route('collaboration.tasks.show', $task->uuid) }}" style="color:inherit;text-decoration:none;hover:underline">
            {{ $task->title }}
          </a>
        </div>
        @if($task->due_date)
          <div class="task-meta {{ $overdue ? 'due-overdue' : '' }}">
            {{ $overdue ? 'Overdue' : 'Due' }} {{ $task->due_date->format('M j') }}
          </div>
        @endif
        <span class="badge" style="background:{{ $task->statusEnum()->bgColor() }};color:{{ $task->statusEnum()->color() }}">
          {{ $task->statusEnum()->label() }}
        </span>
      </div>
    @empty
      <div class="empty-state">No open tasks assigned to you.</div>
    @endforelse
  </div>

  {{-- Pending Approvals --}}
  <div class="section-card">
    <div class="section-header">
      <span class="section-title">Pending Approvals</span>
      <a href="{{ route('collaboration.approvals.index') }}" class="see-all">See all</a>
    </div>
    @forelse($pendingApprovals as $approval)
      <div class="approval-row">
        <div class="approval-title">
          <a href="{{ route('collaboration.approvals.show', $approval->uuid) }}" style="color:inherit;text-decoration:none">
            {{ $approval->post->title ?? 'Untitled Post' }}
          </a>
          <div style="font-size:.75rem;color:rgba(2,27,46,.45);margin-top:.1rem">
            by {{ $approval->requester->name }} · {{ $approval->created_at->diffForHumans() }}
          </div>
        </div>
        @can('post.approve')
        <div style="display:flex;gap:.35rem">
          <form method="POST" action="{{ route('collaboration.approvals.approve', $approval->uuid) }}">
            @csrf
            <button type="submit" class="btn-xs btn-approve">Approve</button>
          </form>
          <a href="{{ route('collaboration.approvals.show', $approval->uuid) }}" class="btn-xs" style="background:rgba(107,114,128,.1);color:#374151;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center">
            Review
          </a>
        </div>
        @endcan
      </div>
    @empty
      <div class="empty-state">No pending approvals.</div>
    @endforelse
  </div>

</div>

{{-- Recent Activity --}}
<div class="section-card">
  <div class="section-header">
    <span class="section-title">Recent Activity</span>
    <a href="{{ route('collaboration.activity') }}" class="see-all">Full log</a>
  </div>
  @forelse($recentActivity as $log)
    @php
      $moduleColors = ['tasks'=>'#3b82f6','approvals'=>'#f59e0b','comments'=>'#10b981','posts'=>'#8b5cf6','team'=>'#65a1d8'];
      $mc = $moduleColors[$log->module] ?? '#6b7280';
    @endphp
    <div class="activity-item">
      <div class="activity-dot" style="background:{{ $mc }}"></div>
      <div class="activity-body">
        <div class="activity-desc">
          <span class="module-chip" style="background:{{ $mc }}18;color:{{ $mc }}">{{ $log->module }}</span>
          {{ $log->description }}
        </div>
        <div class="activity-time">
          @if($log->user) {{ $log->user->name }} · @endif {{ $log->created_at->diffForHumans() }}
        </div>
      </div>
    </div>
  @empty
    <div class="empty-state">No activity yet.</div>
  @endforelse
</div>

@endsection
