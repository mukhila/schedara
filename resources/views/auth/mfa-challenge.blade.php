@extends('layouts.auth')
@section('title', 'Two-Factor Verification')

@section('content')
<div class="auth-logo">
  <img src="{{ asset('logo.png') }}" alt="Schedara">
</div>

<div style="text-align:center;margin-bottom:1.5rem">
  <div style="width:56px;height:56px;background:rgba(101,161,216,.1);border:1px solid rgba(101,161,216,.25);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
    <svg width="26" height="26" fill="none" stroke="var(--brand)" stroke-width="1.8" viewBox="0 0 24 24">
      <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
      <line x1="12" y1="18" x2="12.01" y2="18"/>
    </svg>
  </div>
  <h1 class="auth-title">Two-factor authentication</h1>
  <p class="auth-sub">Open your authenticator app and enter the<br>6-digit code for Schedara</p>
</div>

@if($errors->has('code'))
  <div class="alert alert-error">{{ $errors->first('code') }}</div>
@endif

<form method="POST" action="{{ route('auth.mfa.challenge.post') }}">
  @csrf

  <div class="otp-group" aria-label="Authenticator code">
    @for($i = 0; $i < 6; $i++)
      <input class="otp-digit" type="text" inputmode="numeric" maxlength="1"
             autocomplete="one-time-code" aria-label="Digit {{ $i+1 }}">
    @endfor
  </div>
  <input type="hidden" name="code" id="otp_hidden">

  <button type="submit" class="btn-primary" id="verify-btn" disabled>Verify &amp; sign in</button>
</form>

<div class="auth-footer" style="margin-top:1.25rem">
  <a href="{{ route('auth.login') }}">← Use a different account</a>
</div>
@endsection

@section('scripts')
<script>
const digits = document.querySelectorAll('.otp-digit');
const btn = document.getElementById('verify-btn');
digits.forEach(d => d.addEventListener('input', () => {
  btn.disabled = ![...digits].every(d => d.value.length === 1);
}));
if (digits[0]) digits[0].focus();
</script>
@endsection
