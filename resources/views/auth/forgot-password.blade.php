@extends('layouts.auth')
@section('title', 'Forgot Password')

@section('content')
<div class="auth-logo">
  <img src="{{ asset('logo.png') }}" alt="Schedara">
</div>
<h1 class="auth-title">Reset your password</h1>
<p class="auth-sub">Enter your email and we'll send a 6-digit reset code</p>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('auth.forgot-password.post') }}">
  @csrf

  <div class="form-group">
    <label for="email">Email address</label>
    <div class="input-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
      <input type="email" id="email" name="email" value="{{ old('email') }}"
             placeholder="you@company.com" autocomplete="email" autofocus required>
    </div>
  </div>

  <button type="submit" class="btn-primary">Send reset code</button>
</form>

<div class="auth-footer" style="margin-top:1.25rem">
  <a href="{{ route('auth.login') }}">← Back to sign in</a>
</div>
@endsection
