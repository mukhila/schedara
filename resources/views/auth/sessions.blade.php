@extends('layouts.auth')
@section('title', 'Active Sessions')

@section('head')
<style>
.auth-card{max-width:720px}
.empty-state{text-align:center;padding:2.5rem 1rem;color:var(--muted)}
.empty-state svg{margin-bottom:1rem;opacity:.4}
</style>
@endsection

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem">
  <div>
    <h1 style="font-size:1.25rem;font-weight:800;margin-bottom:.25rem">Active API Sessions</h1>
    <p style="font-size:.85rem;color:var(--muted)">Manage tokens issued for API / mobile access</p>
  </div>
  @if($tokens->isNotEmpty())
    <form method="POST" action="{{ route('auth.sessions.revoke-all') }}">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn-danger" onclick="return confirm('Revoke all API sessions?')">
        Revoke all
      </button>
    </form>
  @endif
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($tokens->isEmpty())
  <div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
      <line x1="12" y1="18" x2="12.01" y2="18"/>
    </svg>
    <p style="font-weight:600;margin-bottom:.5rem">No active API sessions</p>
    <p style="font-size:.8rem">API tokens appear here when you sign in via the API or mobile app.</p>
  </div>
@else
  <table class="sessions-table">
    <thead>
      <tr>
        <th>Session</th>
        <th>Last active</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($tokens as $token)
      <tr>
        <td>
          <div class="token-name">
            {{ $token->name }}
            @if($token->id === auth()->user()->currentAccessToken()?->id)
              <span class="badge-current">current</span>
            @endif
          </div>
          @if($token->expires_at)
            <div class="token-meta">Expires {{ $token->expires_at->diffForHumans() }}</div>
          @endif
        </td>
        <td>
          <div style="color:var(--paper)">{{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</div>
        </td>
        <td>
          <div style="color:var(--muted);font-size:.8rem">{{ $token->created_at->format('M j, Y') }}</div>
        </td>
        <td>
          <form method="POST" action="{{ route('auth.sessions.revoke', $token->id) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-ghost">Revoke</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
@endif

<div class="auth-footer" style="margin-top:2rem">
  <a href="{{ route('dashboard') }}">← Back to dashboard</a>
</div>
@endsection
