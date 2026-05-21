@extends('layouts.auth')
@section('title', 'Team Invitation')

@section('content')
<div class="auth-logo">
  <img src="{{ asset('logo.png') }}" alt="Schedara">
</div>

@if($state === 'expired')
  <div style="text-align:center;padding:1.5rem 0">
    <div style="width:56px;height:56px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
      <svg width="26" height="26" fill="none" stroke="#f87171" stroke-width="1.8" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
    </div>
    <h1 class="auth-title">Invitation expired</h1>
    <p class="auth-sub">This invitation link has expired or has already been used.<br>Ask the team to send a new invitation.</p>
  </div>
  <a href="{{ route('home') }}" class="btn-primary" style="display:block;text-align:center;text-decoration:none;margin-top:1rem">
    Go to homepage
  </a>

@elseif($state === 'mismatch')
  <div style="text-align:center;padding:1.5rem 0">
    <div style="width:56px;height:56px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
      <svg width="26" height="26" fill="none" stroke="#f59e0b" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
      </svg>
    </div>
    <h1 class="auth-title">Wrong account</h1>
    <p class="auth-sub">
      This invitation was sent to <strong style="color:var(--brand)">{{ $invitation->email }}</strong>.<br>
      You're currently signed in as a different email address.
    </p>
  </div>
  <form method="POST" action="{{ route('auth.logout') }}">
    @csrf
    <button type="submit" class="btn-primary" style="margin-top:1rem">
      Sign out &amp; use the correct account
    </button>
  </form>

@else
  @php
    $role   = $invitation->roleEnum();
    $tenant = $invitation->tenant;
    $inviter = $invitation->inviter;
  @endphp

  <div style="text-align:center;padding:.5rem 0 1.5rem">
    <div style="width:56px;height:56px;background:rgba(101,161,216,.1);border:1px solid rgba(101,161,216,.25);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
      <svg width="26" height="26" fill="none" stroke="var(--brand)" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
      </svg>
    </div>
    <h1 class="auth-title" style="font-size:1.3rem">You're invited!</h1>
  </div>

  <div style="background:rgba(255,255,255,.03);border:1px solid rgba(101,161,216,.15);border-radius:14px;padding:1.25rem;margin-bottom:1.5rem">
    <div style="display:flex;align-items:flex-start;gap:.875rem">
      <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,rgba(101,161,216,.3),rgba(2,27,46,.5));display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--brand);flex-shrink:0">
        {{ strtoupper(substr($inviter->name, 0, 1)) }}
      </div>
      <div>
        <div style="font-size:.9rem;line-height:1.5;color:var(--paper)">
          <strong>{{ $inviter->name }}</strong> invited you to join
          <strong style="color:var(--brand)">{{ $tenant->name }}</strong> as
        </div>
        <div style="margin-top:.4rem">
          <span style="font-size:.8rem;font-weight:700;padding:.2rem .6rem;border-radius:6px;background:{{ $role->badgeColor() }}22;color:{{ $role->badgeColor() }};border:1px solid {{ $role->badgeColor() }}44">
            {{ $role->label() }}
          </span>
        </div>
        @if($invitation->message)
          <p style="font-size:.85rem;color:var(--muted);margin-top:.75rem;font-style:italic">
            "{{ $invitation->message }}"
          </p>
        @endif
      </div>
    </div>
  </div>

  <p style="text-align:center;font-size:.8rem;color:var(--muted);margin-bottom:1.25rem">
    Invited to <strong>{{ $invitation->email }}</strong> • Expires {{ $invitation->expires_at->diffForHumans() }}
  </p>

  <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}" style="margin-bottom:.75rem">
    @csrf
    <button type="submit" class="btn-primary">Accept invitation</button>
  </form>

  <form method="POST" action="{{ route('invitation.decline', $invitation->token) }}">
    @csrf
    <button type="submit" class="btn-ghost" style="width:100%;padding:.75rem">Decline</button>
  </form>
@endif

@endsection
