@extends('layouts.backend')
@section('page_title', 'Approval Center')

@section('styles')
<style>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem}
.page-title{font-size:1.25rem;font-weight:900;color:#021b2e}
.stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem}
.stat-card{background:#fff;border:1px solid rgba(2,27,46,.08);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:.875rem}
.stat-icon{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.stat-body{flex:1}
.stat-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:rgba(2,27,46,.45)}
.stat-val{font-size:1.5rem;font-weight:900;color:#021b2e;line-height:1.1}
.tab-bar{display:flex;gap:0;border-bottom:2px solid rgba(2,27,46,.07);margin-bottom:1.25rem}
.tab{padding:.65rem 1.25rem;font-size:.875rem;font-weight:700;color:rgba(2,27,46,.4);text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.15s}
.tab.active{color:#65a1d8;border-bottom-color:#65a1d8}
.tab:hover{color:#021b2e}
.approval-card{background:#fff;border:1px solid rgba(2,27,46,.08);border-radius:12px;padding:1.25rem;margin-bottom:.875rem;display:flex;gap:1rem;align-items:flex-start;flex-wrap:wrap}
.post-thumb{width:60px;height:60px;border-radius:9px;background:rgba(2,27,46,.06);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0}
.approval-info{flex:1;min-width:0}
.approval-title{font-size:.95rem;font-weight:800;color:#021b2e;margin-bottom:.25rem}
.approval-meta{font-size:.8rem;color:rgba(2,27,46,.45);margin-bottom:.5rem}
.approval-comment{font-size:.82rem;color:rgba(2,27,46,.65);background:rgba(2,27,46,.04);padding:.5rem .75rem;border-radius:7px;margin-bottom:.65rem;border-left:3px solid rgba(101,161,216,.4)}
.approval-actions{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-top:.25rem}
.status-badge{font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:5px}
.btn-approve{background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.25);font-weight:700;padding:.4rem .875rem;border-radius:7px;cursor:pointer;font-family:inherit;font-size:.82rem}
.btn-approve:hover{background:rgba(16,185,129,.2)}
.btn-reject{background:rgba(239,68,68,.08);color:#dc2626;border:1px solid rgba(239,68,68,.2);font-weight:700;padding:.4rem .875rem;border-radius:7px;cursor:pointer;font-family:inherit;font-size:.82rem}
.btn-reject:hover{background:rgba(239,68,68,.15)}
.btn-view{background:rgba(2,27,46,.06);color:rgba(2,27,46,.6);border:1px solid rgba(2,27,46,.12);font-weight:600;padding:.4rem .875rem;border-radius:7px;text-decoration:none;font-size:.82rem}
.empty-state{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.07);padding:3rem;text-align:center}
.empty-icon{font-size:2.5rem;margin-bottom:.75rem}
.empty-title{font-size:1rem;font-weight:700;color:#021b2e;margin-bottom:.35rem}
.empty-sub{font-size:.82rem;color:rgba(2,27,46,.4)}
/* Reject modal */
.modal-overlay{position:fixed;inset:0;background:rgba(2,27,46,.4);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:.2s}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal-box{background:#fff;border-radius:16px;padding:1.75rem;width:100%;max-width:440px}
.modal-title{font-size:1rem;font-weight:800;color:#021b2e;margin-bottom:1rem}
.form-control{width:100%;padding:.6rem .875rem;border:1px solid rgba(2,27,46,.15);border-radius:9px;font-family:inherit;font-size:.875rem;color:#021b2e;outline:none;box-sizing:border-box;resize:vertical}
.form-control:focus{border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.1)}
.modal-actions{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem}
.btn-cancel{background:transparent;color:rgba(2,27,46,.5);font-weight:600;padding:.5rem 1rem;border-radius:8px;border:1px solid rgba(2,27,46,.15);cursor:pointer;font-family:inherit;font-size:.875rem}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}
.flash-success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.25);color:#15803d}
.flash-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#dc2626}
@media(max-width:600px){.stats-row{grid-template-columns:1fr}}
</style>
@endsection

@section('content')

@if(session('success'))
  <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="flash flash-error">{{ $errors->first() }}</div>
@endif

<div class="page-header">
  <div>
    <div class="page-title">Approval Center</div>
  </div>
  <a href="{{ route('collaboration.dashboard') }}" style="font-size:.82rem;color:rgba(2,27,46,.45);text-decoration:none">← Collaboration</a>
</div>

{{-- Stats --}}
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(245,158,11,.1)">⏳</div>
    <div class="stat-body">
      <div class="stat-label">Pending</div>
      <div class="stat-val" style="color:#d97706">{{ $stats['pending'] }}</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.1)">✓</div>
    <div class="stat-body">
      <div class="stat-label">Approved</div>
      <div class="stat-val" style="color:#059669">{{ $stats['approved'] }}</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(239,68,68,.1)">✕</div>
    <div class="stat-body">
      <div class="stat-label">Rejected</div>
      <div class="stat-val" style="color:#dc2626">{{ $stats['rejected'] }}</div>
    </div>
  </div>
</div>

{{-- Tabs --}}
<div class="tab-bar">
  <a href="{{ route('collaboration.approvals.index', ['tab' => 'pending']) }}" class="tab {{ $tab === 'pending' ? 'active' : '' }}">
    Pending @if($stats['pending'] > 0)<span style="background:#ef4444;color:#fff;border-radius:10px;font-size:.65rem;padding:.05rem .3rem;margin-left:.3rem">{{ $stats['pending'] }}</span>@endif
  </a>
  <a href="{{ route('collaboration.approvals.index', ['tab' => 'all']) }}" class="tab {{ $tab === 'all' ? 'active' : '' }}">All</a>
</div>

{{-- Approvals list --}}
@forelse($approvals as $approval)
  @php
    $statusColors = ['pending'=>'#f59e0b','approved'=>'#10b981','rejected'=>'#ef4444','cancelled'=>'#6b7280'];
    $sc = $statusColors[$approval->status] ?? '#6b7280';
  @endphp
  <div class="approval-card">
    <div class="post-thumb">📝</div>
    <div class="approval-info">
      <div class="approval-title">{{ $approval->post->title ?? 'Untitled Post' }}</div>
      <div class="approval-meta">
        Requested by <strong>{{ $approval->requester->name }}</strong>
        · {{ $approval->created_at->diffForHumans() }}
        @if($approval->reviewer)
          · Reviewed by <strong>{{ $approval->reviewer->name }}</strong>
        @endif
      </div>
      @if($approval->request_comment)
        <div class="approval-comment">{{ $approval->request_comment }}</div>
      @endif
      @if($approval->reviewer_comment && $approval->status !== 'pending')
        <div class="approval-comment" style="border-left-color:{{ $sc }}60">{{ $approval->reviewer_comment }}</div>
      @endif
      <div class="approval-actions">
        <span class="status-badge" style="background:{{ $sc }}15;color:{{ $sc }};border:1px solid {{ $sc }}30">
          {{ ucfirst($approval->status) }}
        </span>
        @if($approval->isPending())
          @can('post.approve')
            <form method="POST" action="{{ route('collaboration.approvals.approve', $approval->uuid) }}" style="display:inline">
              @csrf
              <button type="submit" class="btn-approve">✓ Approve</button>
            </form>
            <button type="button" class="btn-reject" onclick="openRejectModal('{{ $approval->uuid }}')">✕ Reject</button>
          @endcan
        @endif
        <a href="{{ route('collaboration.approvals.show', $approval->uuid) }}" class="btn-view">View</a>
      </div>
    </div>
  </div>
@empty
  <div class="empty-state">
    <div class="empty-icon">✓</div>
    <div class="empty-title">{{ $tab === 'pending' ? 'All caught up!' : 'No approvals yet' }}</div>
    <div class="empty-sub">{{ $tab === 'pending' ? 'No posts are waiting for your review.' : 'Post approvals will appear here.' }}</div>
  </div>
@endforelse

{{ $approvals->links() }}

{{-- Reject Modal --}}
<div class="modal-overlay" id="rejectModal">
  <div class="modal-box">
    <div class="modal-title">Reject Post</div>
    <form method="POST" id="rejectForm">
      @csrf
      <label style="font-size:.75rem;font-weight:700;color:rgba(2,27,46,.55);display:block;margin-bottom:.35rem">Rejection reason *</label>
      <textarea name="reason" class="form-control" rows="4" required placeholder="Explain why this post needs revisions…"></textarea>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
        <button type="submit" class="btn-reject" style="font-size:.875rem">Reject Post</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('scripts')
<script>
function openRejectModal(uuid) {
  document.getElementById('rejectForm').action = '/collaboration/approvals/' + uuid + '/reject';
  document.getElementById('rejectModal').classList.add('open');
}
function closeRejectModal() {
  document.getElementById('rejectModal').classList.remove('open');
}
document.getElementById('rejectModal').addEventListener('click', function(e) {
  if (e.target === this) closeRejectModal();
});
</script>
@endsection
