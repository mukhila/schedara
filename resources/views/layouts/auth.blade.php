<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Schedara') — Smart Scheduling</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#021b2e;
  --brand:#65a1d8;
  --paper:#f5fefe;
  --ink2:#0d2f4a;
  --muted:rgba(245,254,254,.5);
  --dim:rgba(101,161,216,.15);
  --err:#f87171;
  --ok:#4ade80;
}
html,body{height:100%}
body{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:var(--ink);
  color:var(--paper);
  display:flex;
  flex-direction:column;
  min-height:100vh;
}

/* ── Nav ── */
.auth-nav{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:1.25rem 2rem;
  border-bottom:1px solid var(--dim);
}
.auth-nav a{text-decoration:none}
.logo-wrap{display:flex;align-items:center;gap:.5rem}
.logo-wrap img{height:32px}
.nav-links{display:flex;gap:1.5rem;align-items:center;font-size:.875rem}
.nav-links a{color:var(--muted);transition:color .2s}
.nav-links a:hover{color:var(--paper)}
.nav-links .btn-outline{
  border:1px solid var(--dim);
  padding:.4rem 1rem;
  border-radius:8px;
  color:var(--brand);
}
.nav-links .btn-outline:hover{background:var(--dim)}

/* ── Wrap ── */
.auth-wrap{
  flex:1;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:2rem 1rem;
  position:relative;
  overflow:hidden;
}
.auth-wrap::before{
  content:'';
  position:fixed;
  top:-200px;right:-150px;
  width:700px;height:700px;
  background:radial-gradient(circle,rgba(101,161,216,.06) 0%,transparent 70%);
  pointer-events:none;
}
.auth-wrap::after{
  content:'';
  position:fixed;
  bottom:-200px;left:-150px;
  width:600px;height:600px;
  background:radial-gradient(circle,rgba(101,161,216,.04) 0%,transparent 70%);
  pointer-events:none;
}

/* ── Card ── */
.auth-card{
  width:100%;
  max-width:440px;
  background:rgba(255,255,255,.03);
  border:1px solid rgba(101,161,216,.18);
  border-radius:20px;
  padding:2.5rem;
  position:relative;
  z-index:1;
  animation:cardIn .45s ease;
}
@keyframes cardIn{
  from{opacity:0;transform:translateY(18px)}
  to{opacity:1;transform:translateY(0)}
}

/* ── Header ── */
.auth-logo{text-align:center;margin-bottom:1.75rem}
.auth-logo img{height:36px}
.auth-title{font-size:1.45rem;font-weight:800;text-align:center;margin-bottom:.4rem}
.auth-sub{text-align:center;color:var(--muted);font-size:.875rem;margin-bottom:2rem;line-height:1.5}
.auth-email-hint{
  text-align:center;
  font-size:.875rem;
  color:var(--brand);
  margin-bottom:1.75rem;
  font-weight:600;
  word-break:break-word;
}

/* ── Alerts ── */
.alert{
  padding:.75rem 1rem;
  border-radius:10px;
  font-size:.85rem;
  margin-bottom:1.25rem;
  line-height:1.5;
}
.alert-error{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.3);color:#fca5a5}
.alert-success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.25);color:#86efac}
.alert-info{background:rgba(101,161,216,.08);border:1px solid rgba(101,161,216,.25);color:var(--brand)}

/* ── Form ── */
.form-group{margin-bottom:1.25rem}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
label{
  display:block;
  font-size:.8rem;
  font-weight:600;
  color:rgba(245,254,254,.65);
  margin-bottom:.45rem;
  letter-spacing:.02em;
  text-transform:uppercase;
}
.input-wrap{position:relative}
.input-wrap svg{
  position:absolute;left:.9rem;top:50%;transform:translateY(-50%);
  width:16px;height:16px;
  color:rgba(245,254,254,.3);
  pointer-events:none;
}
input[type=email],
input[type=password],
input[type=text]{
  width:100%;
  background:rgba(255,255,255,.04);
  border:1px solid rgba(101,161,216,.18);
  border-radius:10px;
  padding:.75rem 1rem .75rem 2.6rem;
  color:var(--paper);
  font-size:.9rem;
  font-family:inherit;
  transition:border-color .2s,background .2s,box-shadow .2s;
  outline:none;
}
input[type=email]:focus,
input[type=password]:focus,
input[type=text]:focus{
  border-color:var(--brand);
  background:rgba(101,161,216,.06);
  box-shadow:0 0 0 3px rgba(101,161,216,.1);
}
input.no-icon{padding-left:1rem}
.field-error{color:var(--err);font-size:.8rem;margin-top:.35rem}

/* ── Password toggle ── */
.pass-wrap{position:relative}
.pass-toggle{
  position:absolute;right:.9rem;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;
  color:rgba(245,254,254,.3);padding:0;
  transition:color .2s;
}
.pass-toggle:hover{color:var(--muted)}

/* ── Row helpers ── */
.row-between{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem}
.check-row{display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:var(--muted)}
.check-row input[type=checkbox]{accent-color:var(--brand)}
.link-sm{font-size:.8rem;color:var(--brand);text-decoration:none;font-weight:600}
.link-sm:hover{text-decoration:underline}

/* ── Buttons ── */
.btn-primary{
  width:100%;
  background:var(--brand);
  color:var(--ink);
  border:none;
  border-radius:10px;
  padding:.875rem;
  font-size:.95rem;
  font-weight:700;
  cursor:pointer;
  transition:opacity .2s,transform .1s,box-shadow .2s;
  font-family:inherit;
  letter-spacing:.01em;
}
.btn-primary:hover{opacity:.9;transform:translateY(-1px);box-shadow:0 6px 20px rgba(101,161,216,.25)}
.btn-primary:active{transform:translateY(0)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}

.btn-google{
  width:100%;
  background:rgba(255,255,255,.05);
  color:var(--paper);
  border:1px solid rgba(255,255,255,.1);
  border-radius:10px;
  padding:.75rem;
  font-size:.875rem;
  font-weight:600;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:.75rem;
  transition:background .2s,border-color .2s;
  text-decoration:none;
  font-family:inherit;
}
.btn-google:hover{background:rgba(255,255,255,.09);border-color:rgba(255,255,255,.18)}
.btn-google svg{flex-shrink:0}

.btn-danger{
  background:rgba(248,113,113,.12);
  color:#fca5a5;
  border:1px solid rgba(248,113,113,.3);
  border-radius:8px;
  padding:.5rem 1rem;
  font-size:.8rem;
  font-weight:600;
  cursor:pointer;
  font-family:inherit;
  transition:background .2s;
}
.btn-danger:hover{background:rgba(248,113,113,.2)}

.btn-ghost{
  background:none;
  color:var(--muted);
  border:1px solid var(--dim);
  border-radius:8px;
  padding:.5rem 1rem;
  font-size:.8rem;
  font-weight:600;
  cursor:pointer;
  font-family:inherit;
  transition:color .2s,border-color .2s;
}
.btn-ghost:hover{color:var(--paper);border-color:rgba(101,161,216,.35)}

/* ── Divider ── */
.divider{
  display:flex;align-items:center;gap:.75rem;
  margin:1.25rem 0;
  color:var(--muted);font-size:.78rem;
}
.divider::before,.divider::after{
  content:'';flex:1;height:1px;
  background:rgba(101,161,216,.13);
}

/* ── Footer ── */
.auth-footer{
  text-align:center;
  margin-top:1.5rem;
  font-size:.85rem;
  color:var(--muted);
}
.auth-footer a{color:var(--brand);text-decoration:none;font-weight:600}
.auth-footer a:hover{text-decoration:underline}

/* ── OTP inputs ── */
.otp-group{
  display:flex;gap:.65rem;justify-content:center;
  margin-bottom:1.5rem;
}
.otp-digit{
  width:48px;height:56px;
  background:rgba(255,255,255,.04);
  border:1px solid rgba(101,161,216,.2);
  border-radius:10px;
  color:var(--paper);
  font-size:1.4rem;
  font-weight:700;
  text-align:center;
  font-family:inherit;
  outline:none;
  transition:border-color .2s,background .2s,box-shadow .2s;
}
.otp-digit:focus{
  border-color:var(--brand);
  background:rgba(101,161,216,.08);
  box-shadow:0 0 0 3px rgba(101,161,216,.12);
}

/* ── QR box ── */
.qr-box{
  display:flex;flex-direction:column;align-items:center;
  gap:1rem;margin:1.5rem 0;
}
.qr-frame{
  background:#fff;
  border-radius:12px;
  padding:10px;
  display:inline-flex;
}
.qr-frame svg{display:block}
.secret-key{
  font-family:monospace;
  font-size:.9rem;
  letter-spacing:.12em;
  background:rgba(255,255,255,.05);
  border:1px solid var(--dim);
  border-radius:8px;
  padding:.5rem .875rem;
  color:var(--brand);
  word-break:break-all;
  text-align:center;
}

/* ── Sessions table ── */
.sessions-table{width:100%;border-collapse:collapse;font-size:.875rem}
.sessions-table th{
  text-align:left;padding:.75rem 1rem;
  color:var(--muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;
  border-bottom:1px solid var(--dim);
}
.sessions-table td{
  padding:.875rem 1rem;
  border-bottom:1px solid rgba(101,161,216,.08);
  vertical-align:middle;
}
.sessions-table tr:last-child td{border-bottom:none}
.token-name{font-weight:600;color:var(--paper)}
.token-meta{font-size:.78rem;color:var(--muted);margin-top:.2rem}
.badge-current{
  font-size:.7rem;font-weight:700;
  background:rgba(74,222,128,.12);
  color:#86efac;border:1px solid rgba(74,222,128,.25);
  border-radius:4px;padding:.1rem .45rem;
  margin-left:.5rem;vertical-align:middle;
}

/* ── MFA status card ── */
.mfa-status{
  display:flex;align-items:flex-start;gap:1rem;
  background:rgba(255,255,255,.03);
  border:1px solid var(--dim);
  border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;
}
.mfa-status-icon{
  width:40px;height:40px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.mfa-status-icon.on{background:rgba(74,222,128,.12)}
.mfa-status-icon.off{background:rgba(248,113,113,.1)}
.mfa-status-body h3{font-size:.95rem;font-weight:700;margin-bottom:.25rem}
.mfa-status-body p{font-size:.82rem;color:var(--muted);line-height:1.5}

.step-label{
  display:flex;align-items:center;gap:.75rem;
  font-size:.8rem;font-weight:700;color:var(--muted);
  text-transform:uppercase;letter-spacing:.06em;
  margin-bottom:1rem;margin-top:1.5rem;
}
.step-label::before{
  content:attr(data-step);
  width:22px;height:22px;
  background:var(--dim);
  border:1px solid rgba(101,161,216,.3);
  border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:.75rem;color:var(--brand);flex-shrink:0;
}

@media(max-width:480px){
  .auth-card{padding:1.75rem 1.25rem}
  .form-row{grid-template-columns:1fr}
  .otp-digit{width:42px;height:50px;font-size:1.2rem}
}
</style>
@yield('head')
</head>
<body>

<nav class="auth-nav">
  <a href="{{ route('home') }}" class="logo-wrap">
    <img src="{{ asset('logo.png') }}" alt="Schedara">
  </a>
  <div class="nav-links">
    @yield('nav_links')
  </div>
</nav>

<div class="auth-wrap">
  <div class="auth-card">
    @yield('content')
  </div>
</div>

<script>
// OTP auto-advance
document.querySelectorAll('.otp-digit').forEach((el, idx, arr) => {
  el.addEventListener('input', () => {
    el.value = el.value.replace(/\D/g,'').slice(-1);
    if (el.value && arr[idx+1]) arr[idx+1].focus();
    syncOtpHidden();
  });
  el.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !el.value && arr[idx-1]) arr[idx-1].focus();
    if (e.key === 'ArrowLeft' && arr[idx-1]) arr[idx-1].focus();
    if (e.key === 'ArrowRight' && arr[idx+1]) arr[idx+1].focus();
  });
  el.addEventListener('paste', e => {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
    [...pasted].slice(0, arr.length - idx).forEach((ch, i) => {
      if (arr[idx+i]) arr[idx+i].value = ch;
    });
    const next = arr[Math.min(idx + pasted.length, arr.length-1)];
    if (next) next.focus();
    syncOtpHidden();
  });
});

function syncOtpHidden() {
  const hidden = document.getElementById('otp_hidden');
  if (!hidden) return;
  hidden.value = [...document.querySelectorAll('.otp-digit')].map(d => d.value).join('');
}

// Password toggle
document.querySelectorAll('.pass-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    const inp = btn.previousElementSibling;
    const isPass = inp.type === 'password';
    inp.type = isPass ? 'text' : 'password';
    btn.innerHTML = isPass
      ? `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
      : `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
  });
});
</script>
@yield('scripts')
</body>
</html>
