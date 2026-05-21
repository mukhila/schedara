@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<div class="auth-logo">
  <img src="{{ asset('logo.png') }}" alt="Schedara">
</div>
<h1 class="auth-title">Welcome to Schedara</h1>
<p class="auth-sub">Sign in or create your free account</p>

{{-- Flash / validation messages --}}
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
  <div class="alert alert-info">{{ session('info') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-error">
    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
  </div>
@endif

{{-- ── Social OAuth buttons ── --}}
<div class="social-stack">
  <a href="{{ route('auth.google') }}" class="btn-social btn-google-full">
    <span class="social-icon">
      <svg width="18" height="18" viewBox="0 0 24 24">
        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
      </svg>
    </span>
    Continue with Google
  </a>

  <a href="{{ route('auth.microsoft') }}" class="btn-social btn-microsoft-full">
    <span class="social-icon">
      <svg width="18" height="18" viewBox="0 0 23 23">
        <rect x="1"  y="1"  width="10" height="10" fill="#f25022"/>
        <rect x="12" y="1"  width="10" height="10" fill="#7fba00"/>
        <rect x="1"  y="12" width="10" height="10" fill="#00a4ef"/>
        <rect x="12" y="12" width="10" height="10" fill="#ffb900"/>
      </svg>
    </span>
    Continue with Microsoft
  </a>

  <a href="{{ route('auth.facebook') }}" class="btn-social btn-facebook-full">
    <span class="social-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
        <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.931-1.956 1.886v2.268h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
      </svg>
    </span>
    Continue with Facebook
  </a>
</div>

<div class="divider">or continue with email</div>

{{-- ── Passwordless email OTP flow ── --}}
<div id="step-email">
  <div class="form-group">
    <label for="email-input">Email address</label>
    <div class="input-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
      <input type="email" id="email-input" placeholder="you@company.com"
             autocomplete="email" autofocus value="{{ old('email') }}">
    </div>
    <div id="email-error" class="field-error" style="display:none"></div>
  </div>
  <button type="button" id="continue-btn" class="btn-primary">Continue →</button>
</div>

{{-- ── Step 2: shown via JS for new users (name required) ── --}}
<div id="step-name" style="display:none">
  <div class="new-user-badge">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Creating a new account for <span id="display-email" class="email-pill"></span>
  </div>

  <form method="POST" action="{{ route('auth.email-otp') }}" id="otp-form">
    @csrf
    <input type="hidden" name="email" id="hidden-email">

    <div class="form-group">
      <label for="name-input">Your full name</label>
      <div class="input-wrap">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <input type="text" id="name-input" name="name" placeholder="Jane Smith"
               autocomplete="name" required>
      </div>
    </div>

    <button type="submit" class="btn-primary" style="margin-bottom:.75rem">
      Create account &amp; get code →
    </button>
  </form>

  <div style="text-align:center">
    <button type="button" id="back-btn" class="back-link">
      ← Use a different email
    </button>
  </div>
</div>

{{-- ── Hidden form for existing users (submitted by JS) ── --}}
<form method="POST" action="{{ route('auth.email-otp') }}" id="existing-form" style="display:none">
  @csrf
  <input type="hidden" name="email" id="existing-email">
</form>

<p class="auth-footer-terms">
  By continuing you agree to our <a href="#" class="link-sm">Terms</a> and <a href="#" class="link-sm">Privacy Policy</a>
</p>
@endsection

@section('head')
<style>
/* Social buttons */
.social-stack{display:flex;flex-direction:column;gap:.65rem;margin-bottom:.25rem}

.btn-social{
  width:100%;
  display:flex;align-items:center;justify-content:center;gap:.75rem;
  background:rgba(255,255,255,.04);
  border:1px solid rgba(101,161,216,.18);
  border-radius:10px;
  padding:.75rem 1rem;
  color:var(--paper);
  font-size:.875rem;font-weight:600;
  text-decoration:none;font-family:inherit;
  transition:background .2s,border-color .2s,transform .1s;
  cursor:pointer;
}
.btn-social:hover{background:rgba(255,255,255,.08);border-color:rgba(101,161,216,.35);transform:translateY(-1px)}
.btn-social:active{transform:translateY(0)}
.social-icon{display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* New user badge */
.new-user-badge{
  display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;
  background:rgba(101,161,216,.08);border:1px solid rgba(101,161,216,.22);
  border-radius:10px;padding:.65rem .9rem;
  font-size:.8rem;color:rgba(245,254,254,.7);
  margin-bottom:1.25rem;
}
.email-pill{
  background:rgba(101,161,216,.18);
  border-radius:4px;padding:.1rem .45rem;
  color:var(--brand);font-weight:600;
  word-break:break-all;
}

.back-link{
  background:none;border:none;color:var(--muted);font-size:.83rem;
  cursor:pointer;font-family:inherit;padding:.25rem;
  transition:color .2s;
}
.back-link:hover{color:var(--brand)}

.auth-footer-terms{
  text-align:center;font-size:.72rem;color:rgba(245,254,254,.3);
  margin-top:1.25rem;line-height:1.5;
}

/* Step animation */
#step-name{animation:stepIn .25s ease}
@keyframes stepIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

/* Loading spinner on button */
.btn-loading{position:relative;color:transparent!important}
.btn-loading::after{
  content:'';
  position:absolute;top:50%;left:50%;
  width:18px;height:18px;
  margin:-9px 0 0 -9px;
  border:2px solid rgba(2,27,46,.3);
  border-top-color:var(--ink);
  border-radius:50%;
  animation:spin .6s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
@endsection

@section('scripts')
<script>
const emailInput   = document.getElementById('email-input');
const continueBtn  = document.getElementById('continue-btn');
const stepEmail    = document.getElementById('step-email');
const stepName     = document.getElementById('step-name');
const displayEmail = document.getElementById('display-email');
const hiddenEmail  = document.getElementById('hidden-email');
const existingForm = document.getElementById('existing-form');
const existingEmailInput = document.getElementById('existing-email');
const nameInput    = document.getElementById('name-input');
const backBtn      = document.getElementById('back-btn');
const emailError   = document.getElementById('email-error');

// Allow pressing Enter in email field
emailInput.addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); continueBtn.click(); }
});

continueBtn.addEventListener('click', async () => {
  const email = emailInput.value.trim();
  emailError.style.display = 'none';

  if (!email) {
    emailError.textContent = 'Please enter your email address.';
    emailError.style.display = 'block';
    emailInput.focus();
    return;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    emailError.textContent = 'Please enter a valid email address.';
    emailError.style.display = 'block';
    emailInput.focus();
    return;
  }

  continueBtn.classList.add('btn-loading');
  continueBtn.disabled = true;

  try {
    const res = await fetch('{{ route('auth.check-email') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email }),
    });

    const data = await res.json();

    if (data.exists) {
      // Existing user → submit directly, no name needed
      existingEmailInput.value = email;
      existingForm.submit();
    } else {
      // New user → reveal name step
      displayEmail.textContent = email;
      hiddenEmail.value = email;
      stepEmail.style.display = 'none';
      stepName.style.display = 'block';
      nameInput.focus();
    }
  } catch {
    // On network error, fall back to submitting with email only
    existingEmailInput.value = email;
    existingForm.submit();
  }
});

backBtn.addEventListener('click', () => {
  stepName.style.display = 'none';
  stepEmail.style.display = 'block';
  emailInput.focus();
  continueBtn.classList.remove('btn-loading');
  continueBtn.disabled = false;
});
</script>
@endsection
