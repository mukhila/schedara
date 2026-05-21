@extends('layouts.auth')
@section('title', 'Choose Workspace')

@section('head')
<style>
.workspace-grid{display:grid;gap:.75rem;margin-top:1.5rem}
.workspace-card{
  display:flex;align-items:center;gap:1rem;
  background:rgba(255,255,255,.03);
  border:1px solid rgba(101,161,216,.15);
  border-radius:14px;padding:1.1rem 1.25rem;
  text-decoration:none;color:var(--paper);
  transition:background .2s,border-color .2s,transform .15s;
  cursor:pointer;
}
.workspace-card:hover{
  background:rgba(101,161,216,.07);
  border-color:rgba(101,161,216,.35);
  transform:translateX(3px);
}
.ws-icon{
  width:44px;height:44px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;font-weight:800;flex-shrink:0;
  background:linear-gradient(135deg,rgba(101,161,216,.3),rgba(2,27,46,.5));
  border:1px solid rgba(101,161,216,.2);
  color:var(--brand);
}
.ws-body{flex:1;min-width:0}
.ws-name{font-weight:700;font-size:.95rem;margin-bottom:.2rem;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ws-meta{font-size:.78rem;color:var(--muted);display:flex;align-items:center;gap:.5rem}
.ws-badge{
  font-size:.7rem;font-weight:700;letter-spacing:.04em;
  padding:.1rem .45rem;border-radius:4px;
}
.ws-chevron{color:var(--muted);flex-shrink:0}
</style>
@endsection

@section('content')
<div class="auth-logo">
  <img src="{{ asset('logo.png') }}" alt="Schedara">
</div>
<h1 class="auth-title">Choose a workspace</h1>
<p class="auth-sub">Select the workspace you'd like to enter</p>

@if($errors->has('workspace'))
  <div class="alert alert-error">{{ $errors->first('workspace') }}</div>
@endif

@if($memberships->isEmpty())
  <div style="text-align:center;padding:2rem 0;color:var(--muted)">
    <p>You don't belong to any workspaces yet.</p>
    <p style="font-size:.85rem;margin-top:.5rem">Check your email for an invitation, or create a new account.</p>
  </div>
@else
  <div class="workspace-grid">
    @foreach($memberships as $membership)
      @php
        $tenant = $membership->tenant;
        $role   = \App\Enums\TenantRole::from($membership->role);
        $initial = strtoupper(mb_substr($tenant->name, 0, 1));
      @endphp
      <form method="POST" action="{{ route('workspace.switch', $tenant->id) }}" style="display:contents">
        @csrf
        <button type="submit" class="workspace-card">
          <div class="ws-icon">{{ $initial }}</div>
          <div class="ws-body">
            <div class="ws-name">{{ $tenant->name }}</div>
            <div class="ws-meta">
              <span class="ws-badge" style="background:{{ $role->badgeColor() }}22;color:{{ $role->badgeColor() }};border:1px solid {{ $role->badgeColor() }}44">
                {{ $role->label() }}
              </span>
              @if($tenant->plan)
                <span>{{ $tenant->plan->name }}</span>
              @endif
              @if($tenant->status === 'trialing')
                <span style="color:#f59e0b">Trial • {{ $tenant->trial_ends_at?->diffForHumans() }}</span>
              @endif
            </div>
          </div>
          <svg class="ws-chevron" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </button>
      </form>
    @endforeach
  </div>
@endif

<div class="auth-footer" style="margin-top:2rem">
  <form method="POST" action="{{ route('auth.logout') }}" style="display:inline">
    @csrf
    <button type="submit" style="background:none;border:none;color:var(--brand);font-size:.875rem;font-weight:600;cursor:pointer;font-family:inherit">
      Sign out
    </button>
  </form>
</div>
@endsection
