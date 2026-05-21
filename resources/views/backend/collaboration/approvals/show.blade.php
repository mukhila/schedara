@extends('layouts.backend')
@section('page_title', 'Review Approval')

@section('styles')
<style>
.review-layout{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start}
.main-card{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);padding:1.5rem;margin-bottom:1rem}
.card-title{font-size:.82rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(2,27,46,.45);margin-bottom:1rem}
.post-title{font-size:1.25rem;font-weight:900;color:#021b2e;margin-bottom:.75rem}
.post-content{font-size:.9rem;line-height:1.7;color:rgba(2,27,46,.75);white-space:pre-wrap}
.status-badge{font-size:.75rem;font-weight:700;padding:.25rem .6rem;border-radius:6px;display:inline-block;margin-bottom:.875rem}
.meta-row{display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1rem;font-size:.82rem;color:rgba(2,27,46,.5)}
.meta-row strong{color:#021b2e}
.comment-block{background:rgba(101,161,216,.06);border-left:3px solid #65a1d8;border-radius:0 8px 8px 0;padding:.75rem 1rem;margin-bottom:1rem;font-size:.875rem;color:rgba(2,27,46,.7)}
.rejection-block{background:rgba(239,68,68,.05);border-left:3px solid #ef4444;border-radius:0 8px 8px 0;padding:.75rem 1rem;font-size:.875rem;color:rgba(2,27,46,.7)}
.btn-approve{background:#10b981;color:#fff;font-weight:700;padding:.6rem 1.25rem;border-radius:9px;border:none;cursor:pointer;font-family:inherit;font-size:.875rem;width:100%;margin-bottom:.5rem}
.btn-approve:hover{background:#059669}
.btn-reject{background:rgba(239,68,68,.08);color:#dc2626;font-weight:700;padding:.6rem 1.25rem;border-radius:9px;cursor:pointer;font-family:inherit;font-size:.875rem;width:100%;border:1px solid rgba(239,68,68,.2)}
.btn-reject:hover{background:rgba(239,68,68,.15)}
.form-control{width:100%;padding:.55rem .75rem;border:1px solid rgba(2,27,46,.15);border-radius:8px;font-family:inherit;font-size:.82rem;outline:none;box-sizing:border-box;resize:vertical}
.form-control:focus{border-color:#65a1d8;box-shadow:0 0 0 3px rgba(101,161,216,.15)}
.breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:rgba(2,27,46,.45);margin-bottom:1.25rem}
.breadcrumb a{color:#65a1d8;text-decoration:none}
.breadcrumb-sep{color:rgba(2,27,46,.25)}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}
.flash-success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.25);color:#15803d}
.flash-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#dc2626}
@media(max-width:900px){.review-layout{grid-template-columns:1fr}}
</style>
@endsection

@section('content')

@if(session('success'))
  <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="flash flash-error">{{ $errors->first() }}</div>
@endif

<div class="breadcrumb">
  <a href="{{ route('collaboration.dashboard') }}">Collaboration</a>
  <span class="breadcrumb-sep">›</span>
  <a href="{{ route('collaboration.approvals.index') }}">Approvals</a>
  <span class="breadcrumb-sep">›</span>
  <span>Review</span>
</div>

@php
  $statusColors = ['pending'=>'#f59e0b','approved'=>'#10b981','rejected'=>'#ef4444','cancelled'=>'#6b7280'];
  $sc = $statusColors[$approval->status] ?? '#6b7280';
@endphp

<div class="review-layout">

  {{-- Post content --}}
  <div>
    <div class="main-card">
      <span class="status-badge" style="background:{{ $sc }}15;color:{{ $sc }};border:1px solid {{ $sc }}30">
        {{ ucfirst($approval->status) }}
      </span>
      <div class="post-title">{{ $approval->post->title ?? 'Untitled Post' }}</div>
      <div class="meta-row">
        <span>Requested by <strong>{{ $approval->requester->name }}</strong></span>
        <span>{{ $approval->created_at->format('M j, Y H:i') }}</span>
        @if($approval->reviewer)
          <span>Reviewed by <strong>{{ $approval->reviewer->name }}</strong> on {{ $approval->reviewed_at?->format('M j, Y') }}</span>
        @endif
      </div>

      @if($approval->request_comment)
        <div class="comment-block">
          <strong style="display:block;margin-bottom:.25rem;font-size:.78rem;color:#65a1d8">Requester note:</strong>
          {{ $approval->request_comment }}
        </div>
      @endif

      @if($approval->reviewer_comment)
        <div class="{{ $approval->isRejected() ? 'rejection-block' : 'comment-block' }}" style="{{ $approval->isApproved() ? 'border-left-color:#10b981' : '' }}">
          <strong style="display:block;margin-bottom:.25rem;font-size:.78rem;color:{{ $sc }}">Reviewer feedback:</strong>
          {{ $approval->reviewer_comment }}
        </div>
      @endif

      @if($approval->post->content)
        <div class="card-title" style="margin-top:1.25rem">Post Content</div>
        <div class="post-content">{{ $approval->post->content }}</div>
      @endif

      @if($approval->post->caption)
        <div class="card-title" style="margin-top:1.25rem">Caption</div>
        <div class="post-content">{{ $approval->post->caption }}</div>
      @endif
    </div>
  </div>

  {{-- Sidebar: approve/reject --}}
  <div>
    @if($approval->isPending())
      @can('post.approve')
      <div class="main-card">
        <div class="card-title">Review Decision</div>

        <form method="POST" action="{{ route('collaboration.approvals.approve', $approval->uuid) }}" style="margin-bottom:1rem">
          @csrf
          <div style="margin-bottom:.65rem">
            <label style="font-size:.72rem;font-weight:700;color:rgba(2,27,46,.5);text-transform:uppercase;display:block;margin-bottom:.3rem">Comment (optional)</label>
            <textarea name="comment" class="form-control" rows="2" placeholder="Leave a note for the author…"></textarea>
          </div>
          <button type="submit" class="btn-approve">✓ Approve Post</button>
        </form>

        <form method="POST" action="{{ route('collaboration.approvals.reject', $approval->uuid) }}">
          @csrf
          <div style="margin-bottom:.65rem">
            <label style="font-size:.72rem;font-weight:700;color:rgba(2,27,46,.5);text-transform:uppercase;display:block;margin-bottom:.3rem">Rejection reason *</label>
            <textarea name="reason" class="form-control" rows="3" placeholder="Explain what needs to be changed…" required></textarea>
          </div>
          <button type="submit" class="btn-reject">✕ Reject Post</button>
        </form>
      </div>
      @endcan
    @endif

    <div class="main-card">
      <div class="card-title">Post Details</div>
      <div style="font-size:.82rem;color:rgba(2,27,46,.6);line-height:2">
        <div><span style="color:rgba(2,27,46,.4)">Platform:</span> {{ implode(', ', $approval->post->platforms ?? ['—']) }}</div>
        <div><span style="color:rgba(2,27,46,.4)">Status:</span> {{ ucfirst($approval->post->status) }}</div>
        @if($approval->post->scheduled_at)
          <div><span style="color:rgba(2,27,46,.4)">Scheduled:</span> {{ $approval->post->scheduled_at->format('M j, Y H:i') }}</div>
        @endif
      </div>
    </div>
  </div>

</div>
@endsection
