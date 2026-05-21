@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<div class="auth-logo">
  <img src="{{ asset('logo.png') }}" alt="Schedara">
</div>
<h1 class="auth-title">Set new password</h1>
<p class="auth-sub">Enter the 6-digit code sent to</p>
<div class="auth-email-hint">{{ $email }}</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-error">
    @foreach($errors->all() as $e)
      <div>{{ $e }}</div>
    @endforeach
  </div>
@endif

<form method="POST" action="{{ route('auth.reset-password.post') }}">
  @csrf

  <div class="form-group">
    <label>Reset code</label>
    <div class="otp-group" aria-label="Reset code">
      @for($i = 0; $i < 6; $i++)
        <input class="otp-digit" type="text" inputmode="numeric" maxlength="1"
               autocomplete="off" aria-label="Digit {{ $i+1 }}">
      @endfor
    </div>
    <input type="hidden" name="code" id="otp_hidden">
  </div>

  <div class="form-group">
    <label for="password">New password</label>
    <div class="input-wrap pass-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      <input type="password" id="password" name="password" placeholder="Min 8 characters" autocomplete="new-password" required>
      <button type="button" class="pass-toggle" tabindex="-1">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
  </div>

  <div class="form-group">
    <label for="password_confirmation">Confirm new password</label>
    <div class="input-wrap pass-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      <input type="password" id="password_confirmation" name="password_confirmation"
             placeholder="Repeat password" autocomplete="new-password" required>
      <button type="button" class="pass-toggle" tabindex="-1">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
  </div>

  <button type="submit" class="btn-primary">Reset password</button>
</form>

<div class="auth-footer" style="margin-top:1.25rem">
  <a href="{{ route('auth.forgot-password') }}">← Resend code</a>
</div>
@endsection

@section('scripts')
<script>
if (document.querySelector('.otp-digit')) document.querySelector('.otp-digit').focus();
</script>
@endsection
