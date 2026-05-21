<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Analytics') · Schedara</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  {{-- Tailwind CDN with custom brand config --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
          colors: {
            ink:   '#021b2e',
            paper: '#f5fefe',
            line:  '#e3e9ee',
            brand: {
              50:'#eef5fb', 100:'#dceaf5', 200:'#b4cfe8',
              300:'#8bb4dc', 400:'#65a1d8', 500:'#4a8ccc',
              600:'#2f76bd', 700:'#235b95', 800:'#18406d',
            },
            mint:  '#22B07E',
            gold:  '#FDBB1F',
            coral: '#FF401C',
          },
        },
      },
    };
  </script>

  <style>
  /* ── Reset ───────────────────────────────────────────────── */
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{
    --ink:#021b2e; --paper:#f5fefe; --line:#e3e9ee;
    --brand:#65a1d8; --mint:#22B07E; --gold:#FDBB1F; --coral:#FF401C;
    --sidebar-w:232px;
  }
  html,body{height:100%}
  body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--paper);color:var(--ink);-webkit-font-smoothing:antialiased;}

  /* ── App Shell ───────────────────────────────────────────── */
  .app-shell{display:flex;min-height:100vh}

  /* ── Sidebar ─────────────────────────────────────────────── */
  .app-side{
    width:var(--sidebar-w);background:var(--ink);
    display:flex;flex-direction:column;
    position:fixed;top:0;left:0;bottom:0;z-index:50;
    overflow-y:auto;flex-shrink:0;
    transition:transform .25s ease;
  }
  @media(max-width:768px){
    .app-side{transform:translateX(-100%)}
    .app-side.open{transform:translateX(0)}
  }

  .app-side-brand{
    display:flex;align-items:center;gap:10px;
    padding:18px 14px;
    border-bottom:1px solid rgba(255,255,255,.07);
    text-decoration:none;flex-shrink:0;
  }
  .s-mark{
    width:30px;height:30px;border-radius:7px;
    background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;
  }
  .s-mark svg{width:18px;height:18px}
  .schedara-wing{stroke:#65a1d8;stroke-width:2.4;stroke-linecap:round;fill:none}
  .name{font-size:15px;font-weight:800;color:#fff;letter-spacing:-.3px}

  .app-side-group{
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:1.5px;color:rgba(245,254,254,.3);
    padding:14px 10px 5px;
  }
  .app-side-link{
    display:flex;align-items:center;gap:9px;
    padding:8px 10px;border-radius:8px;
    color:rgba(245,254,254,.6);font-size:13px;font-weight:600;
    text-decoration:none;transition:.15s;margin-bottom:1px;
  }
  .app-side-link:hover{background:rgba(101,161,216,.1);color:#fff}
  .app-side-link.active{background:rgba(101,161,216,.15);color:var(--brand)}
  .app-side-link svg{width:15px;height:15px;flex-shrink:0;opacity:.65}
  .app-side-link.active svg,.app-side-link:hover svg{opacity:1}

  .app-side-footer{padding:10px 12px;border-top:1px solid rgba(255,255,255,.07)}
  .app-side-user{
    display:flex;align-items:center;gap:9px;padding:9px 8px;
    border-radius:8px;text-decoration:none;transition:background .15s;cursor:pointer;
  }
  .app-side-user:hover{background:rgba(255,255,255,.05)}
  .user-av{
    width:32px;height:32px;border-radius:50%;flex-shrink:0;
    background:linear-gradient(135deg,var(--brand),#235b95);
    display:flex;align-items:center;justify-content:center;
    font-size:12px;font-weight:800;color:#fff;
  }

  /* ── Main ────────────────────────────────────────────────── */
  .app-main{
    margin-left:var(--sidebar-w);flex:1;
    display:flex;flex-direction:column;min-height:100vh;
    background:var(--paper);
  }
  @media(max-width:768px){.app-main{margin-left:0}}

  /* ── Topbar ──────────────────────────────────────────────── */
  .app-top{
    background:#fff;border-bottom:1px solid var(--line);
    height:60px;display:flex;align-items:center;justify-content:space-between;
    padding:0 24px;position:sticky;top:0;z-index:40;gap:16px;
  }
  .app-search{
    display:flex;align-items:center;gap:8px;
    background:var(--paper);border:1px solid var(--line);
    border-radius:8px;padding:6px 10px;flex:1;max-width:340px;
  }
  .app-search input{
    border:none;background:none;outline:none;
    font-size:13px;color:var(--ink);width:100%;font-family:inherit;
  }
  .app-search kbd{
    font-size:10px;color:rgba(2,27,46,.35);
    border:1px solid var(--line);border-radius:4px;
    padding:1px 5px;white-space:nowrap;font-family:inherit;
  }
  .app-top-actions{display:flex;align-items:center;gap:8px}
  .app-icon-btn{
    width:36px;height:36px;background:#fff;border:1px solid var(--line);
    border-radius:8px;display:flex;align-items:center;justify-content:center;
    cursor:pointer;position:relative;transition:.15s;color:rgba(2,27,46,.5);
  }
  .app-icon-btn:hover{border-color:rgba(2,27,46,.2);color:var(--ink)}
  .app-icon-btn .dot{
    position:absolute;top:6px;right:6px;
    width:7px;height:7px;background:var(--coral);
    border-radius:50%;border:2px solid #fff;
  }
  .hamburger{display:none}
  @media(max-width:768px){
    .hamburger{display:flex}
    .app-search{max-width:none;flex:1}
  }

  /* ── Content ─────────────────────────────────────────────── */
  .app-content{padding:24px;flex:1}

  /* ── Cards ───────────────────────────────────────────────── */
  .card{background:#fff;border:1px solid var(--line);border-radius:16px}

  /* ── KPI sparkline ───────────────────────────────────────── */
  .kpi-card .spark path{stroke:#65a1d8;stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round}

  /* ── Pills ───────────────────────────────────────────────── */
  .pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 8px;border-radius:9999px;white-space:nowrap}
  .pill-mint {background:rgba(34,176,126,.1);color:#22B07E}
  .pill-coral{background:rgba(255,64,28,.1);color:#FF401C}
  .pill-gold {background:rgba(253,187,31,.15);color:#a37d0a}
  .pill-brand{background:rgba(101,161,216,.12);color:#4a8ccc}
  .pill-dot::before{content:'';display:inline-block;width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0}

  /* ── Channel avatars ─────────────────────────────────────── */
  .chan-ig{background:linear-gradient(135deg,#f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%)}
  .chan-fb{background:#1877F2}
  .chan-x {background:#000}
  .chan-li{background:#0A66C2}
  .chan-tt{background:#000}
  .chan-yt{background:#FF0000}

  /* ── Mobile overlay ──────────────────────────────────────── */
  .sidebar-overlay{
    display:none;position:fixed;inset:0;background:rgba(2,27,46,.5);z-index:49;
  }
  .sidebar-overlay.open{display:block}

  @yield('styles')
  </style>

  @yield('head')
</head>
<body>

{{-- Mobile sidebar overlay --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="app-shell">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="app-side" id="app-side">

    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="app-side-brand">
      <span class="s-mark">
        <svg viewBox="0 0 32 32" class="schedara-wing" style="stroke:#65a1d8">
          <path d="M4 22 C 10 13, 19 10, 28 11"/>
          <path d="M8 22 C 13 16, 20 14, 27 15" opacity=".7"/>
          <path d="M12 22 C 16 19, 22 17, 27 19" opacity=".45"/>
        </svg>
      </span>
      <span class="name">Schedara</span>
    </a>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-3">

      <div class="app-side-group">Insights</div>
      <a href="{{ route('dashboard') }}"
         class="app-side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 14l3-3 4 4 5-7"/></svg>
        Analytics
      </a>
      <a href="#" class="app-side-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        Reports
      </a>

      <div class="app-side-group">Publish</div>
      <a href="#" class="app-side-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>
        Calendar
      </a>
      <a href="#" class="app-side-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M3 12h18"/></svg>
        Composer
      </a>
      <a href="#" class="app-side-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
        Queue
      </a>

      <div class="app-side-group">Engage</div>
      <a href="{{ route('notifications.index') }}"
         class="app-side-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 21a2 2 0 0 0 4 0"/></svg>
        Notifications
        @if(($unread ?? 0) > 0)
          <span class="ml-auto text-[10px] font-bold text-white px-1.5 py-0.5 rounded-full" style="background:var(--coral)">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
      </a>
      <a href="{{ route('notifications.slack') }}"
         class="app-side-link {{ request()->routeIs('notifications.slack*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 10c-.83 0-1.5-.67-1.5-1.5v-5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5z"/><path d="M20.5 10H19V8.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/><path d="M9.5 14c.83 0 1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5S8 21.33 8 20.5v-5c0-.83.67-1.5 1.5-1.5z"/><path d="M3.5 14H5v1.5c0 .83-.67 1.5-1.5 1.5S2 16.33 2 15.5 2.67 14 3.5 14z"/><path d="M14 14.5c0-.83.67-1.5 1.5-1.5h5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-5c-.83 0-1.5-.67-1.5-1.5z"/><path d="M15.5 19H14v1.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z"/><path d="M10 9.5C10 8.67 9.33 8 8.5 8h-5C2.67 8 2 8.67 2 9.5S2.67 11 3.5 11h5c.83 0 1.5-.67 1.5-1.5z"/><path d="M8.5 5H10V3.5C10 2.67 9.33 2 8.5 2S7 2.67 7 3.5 7.67 5 8.5 5z"/></svg>
        Slack
      </a>
      <a href="{{ route('notifications.templates') }}"
         class="app-side-link {{ request()->routeIs('notifications.templates') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Msg Templates
      </a>
      <a href="#" class="app-side-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/></svg>
        Mentions
      </a>

      <div class="app-side-group">Content</div>
      <a href="{{ route('cms.index') }}"
         class="app-side-link {{ request()->routeIs('cms.index') || request()->routeIs('cms.show') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Media Library
      </a>
      <a href="{{ route('cms.approvals') }}"
         class="app-side-link {{ request()->routeIs('cms.approvals') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Approvals
      </a>

      <div class="app-side-group">Publish</div>
      <a href="{{ route('posts.index') }}"
         class="app-side-link {{ request()->routeIs('posts.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        Posts
      </a>
      <a href="{{ route('posts.calendar') }}"
         class="app-side-link {{ request()->routeIs('posts.calendar') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Calendar
      </a>

      <div class="app-side-group">Agency</div>
      <a href="{{ route('agency.dashboard') }}"
         class="app-side-link {{ request()->routeIs('agency.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Clients
      </a>
      <a href="{{ route('portal.dashboard') }}"
         class="app-side-link {{ request()->routeIs('portal.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
        Client Portal
      </a>

      <div class="app-side-group">Workspace</div>
      <a href="{{ route('social.index') }}"
         class="app-side-link {{ request()->routeIs('social.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 10.8l6.8-4M8.6 13.2l6.8 4"/></svg>
        Social Accounts
      </a>
      <a href="{{ route('team.index') }}"
         class="app-side-link {{ request()->routeIs('team.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg>
        Team
      </a>
      <a href="{{ route('billing.index') }}"
         class="app-side-link {{ request()->routeIs('billing.index','billing.plans','billing.checkout','billing.portal','billing.cancel','billing.pause','billing.resume','billing.paypal.callback') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 8h20M6 12h2M10 12h4"/></svg>
        Billing
      </a>
      <a href="{{ route('billing.usage') }}"
         class="app-side-link {{ request()->routeIs('billing.usage') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="8"/><rect x="12" y="6" width="3" height="12"/><rect x="17" y="13" width="3" height="5"/></svg>
        Usage
      </a>
      <a href="{{ route('billing.invoices') }}"
         class="app-side-link {{ request()->routeIs('billing.invoices','billing.invoices.download') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
        Invoices
      </a>
      <a href="{{ route('auth.mfa.setup') }}"
         class="app-side-link {{ request()->routeIs('auth.mfa.*', 'auth.sessions') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 0 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 0 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 0 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 0 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
        Settings
      </a>
    </nav>

    {{-- Footer: workspace + user --}}
    <div class="app-side-footer">
      {{-- Workspace switcher --}}
      @if(app()->bound('current.tenant'))
        <div class="mb-2">
          <div class="app-side-group" style="padding-top:0;padding-bottom:6px">Workspace</div>
          <a href="{{ route('workspace.select') }}" class="app-side-user">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[11px] font-bold flex-shrink-0"
                 style="background:rgba(101,161,216,.2);color:var(--brand)">
              {{ strtoupper(mb_substr(app('current.tenant')->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-xs font-bold text-white truncate">{{ app('current.tenant')->name }}</div>
              <div class="text-[10px] text-white/40">Switch workspace</div>
            </div>
            <svg class="w-3.5 h-3.5 opacity-30 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
          </a>
        </div>
        <div style="height:1px;background:rgba(255,255,255,.07);margin-bottom:8px"></div>
      @endif

      @if(auth()->user()?->is_super_admin)
      <a href="{{ route('admin.dashboard') }}"
         class="app-side-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        Admin Panel
      </a>
      @endif

      {{-- User card + logout --}}
      <div class="flex items-center gap-2 px-2 py-1">
        <div class="user-av">{{ strtoupper(mb_substr(auth()->user()?->name ?? 'U', 0, 1)) }}</div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-bold text-white truncate">{{ auth()->user()?->name ?? 'User' }}</div>
          <div class="text-[11px] text-white/40">
            @if(app()->bound('current.tenant.membership'))
              {{ app('current.tenant.membership')->roleEnum()->label() }}
            @elseif(auth()->user()?->is_super_admin)
              Super Admin
            @else
              Member
            @endif
          </div>
        </div>
        <form method="POST" action="{{ route('auth.logout') }}" class="flex-shrink-0">
          @csrf
          <button type="submit" class="text-white/30 hover:text-white/70 transition-colors p-1" title="Sign out">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </form>
      </div>
    </div>
  </aside>

  {{-- ══ MAIN ══ --}}
  <main class="app-main">

    {{-- Topbar --}}
    <div class="app-top">
      <button class="hamburger app-icon-btn" onclick="openSidebar()">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>

      <div class="app-search">
        <svg class="w-4 h-4 flex-shrink-0" style="color:rgba(2,27,46,.35)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input placeholder="Search reports, posts, channels…" />
        <kbd>⌘K</kbd>
      </div>

      <div class="app-top-actions">
        <button class="app-icon-btn" title="Help">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 1 1 5.8 1c0 2-3 2-3 4M12 17h.01"/></svg>
        </button>
        @include('backend.notifications._bell')
        <button class="bg-ink text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-1.5 hover:bg-brand-800 transition-colors border-0 cursor-pointer font-sans">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 4v12M4 10h12" stroke-linecap="round"/></svg>
          New post
        </button>
      </div>
    </div>

    {{-- Page content --}}
    <div class="app-content">
      @yield('content')
    </div>

  </main>
</div>

<script>
function openSidebar() {
  document.getElementById('app-side').classList.add('open');
  document.getElementById('sidebar-overlay').classList.add('open');
}
function closeSidebar() {
  document.getElementById('app-side').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('open');
}
</script>

@yield('scripts')
</body>
</html>
