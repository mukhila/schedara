@extends('layouts.auth')
@section('title', 'Two-Factor Setup')

@section('content')
<div class="auth-logo">
  <img src="{{ asset('logo.png') }}" alt="Schedara">
</div>
<h1 class="auth-title">Two-Factor Authentication</h1>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($enabled)

  {{-- ── Already Enabled ── --}}
  <div class="mfa-status">
    <div class="mfa-status-icon on">
      <svg width="20" height="20" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        <polyline points="9 12 11 14 15 10"/>
      </svg>
    </div>
    <div class="mfa-status-body">
      <h3 style="color:#4ade80">Two-factor is active</h3>
      <p>Your account is protected with TOTP. You'll need your authenticator app each time you sign in.</p>
    </div>
  </div>

  <form method="POST" action="{{ route('auth.mfa.disable') }}">
    @csrf
    @method('DELETE')
    <p style="font-size:.875rem;color:var(--muted);margin-bottom:1.25rem">
      To disable 2FA, enter your current password and confirm with your authenticator app.
    </p>

    @if($errors->any())
      <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <div class="form-group">
      <label for="password">Current password</label>
      <div class="input-wrap pass-wrap">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <input type="password" id="password" name="password" placeholder="••••••••" required>
        <button type="button" class="pass-toggle" tabindex="-1">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
    </div>

    <div class="form-group">
      <label>Authenticator code</label>
      <div class="otp-group" style="justify-content:flex-start">
        @for($i = 0; $i < 6; $i++)
          <input class="otp-digit" type="text" inputmode="numeric" maxlength="1" autocomplete="off">
        @endfor
      </div>
      <input type="hidden" name="code" id="otp_hidden">
    </div>

    <button type="submit" class="btn-danger" style="width:100%;padding:.75rem;font-size:.9rem">
      Disable two-factor authentication
    </button>
  </form>

@else

  {{-- ── Setup Flow ── --}}
  <div class="mfa-status">
    <div class="mfa-status-icon off">
      <svg width="20" height="20" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
      </svg>
    </div>
    <div class="mfa-status-body">
      <h3>Not yet enabled</h3>
      <p>Add an extra layer of security. You'll need an authenticator app like Google Authenticator or Authy.</p>
    </div>
  </div>

  <p class="step-label" data-step="1">Scan the QR code</p>

  <div class="qr-box">
    <div class="qr-frame">{!! $qrSvg !!}</div>
  </div>

  <p class="step-label" data-step="2">Or enter the key manually</p>
  <div class="secret-key">{{ $segments }}</div>

  <p class="step-label" data-step="3">Confirm with your app</p>

  @if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('auth.mfa.enable') }}">
    @csrf
    <div class="otp-group" style="margin-top:.5rem">
      @for($i = 0; $i < 6; $i++)
        <input class="otp-digit" type="text" inputmode="numeric" maxlength="1"
               autocomplete="one-time-code">
      @endfor
    </div>
    <input type="hidden" name="code" id="otp_hidden">
    <button type="submit" class="btn-primary" id="enable-btn" disabled>Enable two-factor auth</button>
  </form>

@endif

<div class="auth-footer" style="margin-top:1.25rem">
  <a href="{{ route('dashboard') }}">← Back to dashboard</a>
</div>
@endsection

@section('scripts')
<script>
const enableBtn = document.getElementById('enable-btn');
if (enableBtn) {
  document.querySelectorAll('.otp-digit').forEach(d => d.addEventListener('input', () => {
    enableBtn.disabled = ![...document.querySelectorAll('.otp-digit')].every(d => d.value.length === 1);
  }));
}
</script>
@endsection
