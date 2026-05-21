@extends('layouts.auth')
@section('title', 'Enter Verification Code')

@section('content')
<div class="auth-logo">
  <img src="{{ asset('logo.png') }}" alt="Schedara">
</div>

<div class="otp-header">
  <div class="otp-icon-wrap">
    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
      <polyline points="22,6 12,12 2,6"/>
    </svg>
  </div>
  <h1 class="auth-title">Check your inbox</h1>
  <p class="auth-sub" style="margin-bottom:.75rem">We sent a 6-digit verification code to</p>
  <div class="auth-email-hint">{{ $email }}</div>
</div>

{{-- Alerts --}}
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
  <div class="alert alert-info">{{ session('info') }}</div>
@endif
@if($errors->has('code'))
  <div class="alert alert-error">{{ $errors->first('code') }}</div>
@endif
@if($errors->has('resend'))
  <div class="alert alert-error">{{ $errors->first('resend') }}</div>
@endif

{{-- OTP input form --}}
<form method="POST" action="{{ route('auth.verify-email.post') }}" id="otp-verify-form">
  @csrf

  <div class="otp-group" aria-label="Verification code" role="group">
    @for($i = 0; $i < 6; $i++)
      <input class="otp-digit" type="text" inputmode="numeric" maxlength="1"
             autocomplete="off" aria-label="Digit {{ $i + 1 }}">
    @endfor
  </div>

  <input type="hidden" name="code" id="otp_hidden">

  <button type="submit" class="btn-primary" id="verify-btn" disabled>
    Verify &amp; Sign In
  </button>
</form>

{{-- Resend section --}}
<div class="resend-section" id="resend-section">
  <p class="resend-hint" id="resend-hint">
    Didn't receive it? Resend in <strong id="countdown-display">0:{{ str_pad($cooldownSeconds, 2, '0', STR_PAD_LEFT) }}</strong>
  </p>

  <form method="POST" action="{{ route('auth.verify-email.resend') }}" id="resend-form" style="display:none">
    @csrf
    <p class="resend-hint">Didn't receive it?</p>
    <button type="submit" class="btn-resend">Resend code</button>
  </form>
</div>

<div class="auth-footer">
  <a href="{{ route('auth.login') }}">← Use a different email</a>
</div>
@endsection

@section('head')
<style>
.otp-header{text-align:center;margin-bottom:.5rem}

.otp-icon-wrap{
  width:56px;height:56px;
  background:rgba(101,161,216,.1);
  border:1px solid rgba(101,161,216,.25);
  border-radius:16px;
  display:inline-flex;align-items:center;justify-content:center;
  margin-bottom:1.25rem;
  color:var(--brand);
}

.otp-group{
  display:flex;gap:.65rem;justify-content:center;
  margin-bottom:1.5rem;
}
.otp-digit{
  width:50px;height:58px;
  background:rgba(255,255,255,.04);
  border:1.5px solid rgba(101,161,216,.2);
  border-radius:12px;
  color:var(--paper);
  font-size:1.5rem;font-weight:700;
  text-align:center;font-family:inherit;
  outline:none;
  transition:border-color .2s,background .2s,box-shadow .2s,transform .1s;
  caret-color:transparent;
}
.otp-digit:focus{
  border-color:var(--brand);
  background:rgba(101,161,216,.08);
  box-shadow:0 0 0 3px rgba(101,161,216,.14);
  transform:scale(1.04);
}
.otp-digit.filled{
  border-color:rgba(101,161,216,.5);
  background:rgba(101,161,216,.06);
}

.resend-section{
  text-align:center;
  margin-top:1.5rem;
}
.resend-hint{
  font-size:.85rem;
  color:var(--muted);
  margin-bottom:.6rem;
}
.resend-hint strong{color:var(--brand)}

.btn-resend{
  background:none;
  border:1px solid rgba(101,161,216,.3);
  border-radius:8px;
  color:var(--brand);
  font-size:.85rem;font-weight:600;
  cursor:pointer;font-family:inherit;
  padding:.45rem 1.1rem;
  transition:background .2s,border-color .2s;
}
.btn-resend:hover{background:rgba(101,161,216,.1);border-color:rgba(101,161,216,.5)}

@media(max-width:400px){
  .otp-digit{width:42px;height:50px;font-size:1.25rem}
}
</style>
@endsection

@section('scripts')
<script>
// ── OTP digit inputs ──────────────────────────────────────────────────
const digits    = document.querySelectorAll('.otp-digit');
const verifyBtn = document.getElementById('verify-btn');
const otpHidden = document.getElementById('otp_hidden');

function syncAndCheck() {
  const val = [...digits].map(d => d.value).join('');
  otpHidden.value = val;
  verifyBtn.disabled = val.length < 6;
  digits.forEach(d => d.classList.toggle('filled', d.value.length === 1));
}

digits.forEach((el, idx, arr) => {
  el.addEventListener('input', () => {
    el.value = el.value.replace(/\D/g, '').slice(-1);
    if (el.value && arr[idx + 1]) arr[idx + 1].focus();
    syncAndCheck();
  });

  el.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !el.value && arr[idx - 1]) {
      arr[idx - 1].focus();
      arr[idx - 1].value = '';
      syncAndCheck();
    }
    if (e.key === 'ArrowLeft'  && arr[idx - 1]) arr[idx - 1].focus();
    if (e.key === 'ArrowRight' && arr[idx + 1]) arr[idx + 1].focus();
  });

  el.addEventListener('paste', e => {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData)
      .getData('text').replace(/\D/g, '');
    [...pasted].slice(0, arr.length - idx).forEach((ch, i) => {
      if (arr[idx + i]) arr[idx + i].value = ch;
    });
    const next = arr[Math.min(idx + pasted.length, arr.length - 1)];
    if (next) next.focus();
    syncAndCheck();
  });
});

// Auto-focus first digit
if (digits[0]) digits[0].focus();

// ── Resend countdown ──────────────────────────────────────────────────
let remaining = {{ (int) $cooldownSeconds }};

const countdownDisplay = document.getElementById('countdown-display');
const resendHint       = document.getElementById('resend-hint');
const resendForm       = document.getElementById('resend-form');

function formatTime(s) {
  const m = Math.floor(s / 60);
  const sec = s % 60;
  return `${m}:${String(sec).padStart(2, '0')}`;
}

function showResendButton() {
  resendHint.style.display = 'none';
  resendForm.style.display = 'block';
}

if (remaining <= 0) {
  showResendButton();
} else {
  countdownDisplay.textContent = formatTime(remaining);

  const timer = setInterval(() => {
    remaining--;
    if (remaining <= 0) {
      clearInterval(timer);
      showResendButton();
    } else {
      countdownDisplay.textContent = formatTime(remaining);
    }
  }, 1000);
}
</script>
@endsection
