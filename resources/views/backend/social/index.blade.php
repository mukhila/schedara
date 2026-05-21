@extends('layouts.backend')

@section('title', 'Social Accounts')

@section('head')
<style>
  .platform-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;color:white;white-space:nowrap;}
  .platform-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:900;color:white;flex-shrink:0;}
  .status-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
  .badge-active{background:rgba(34,176,126,.12);color:#22B07E;}
  .badge-expired{background:rgba(255,64,28,.1);color:#FF401C;}
  .badge-revoked{background:rgba(107,114,128,.1);color:#6B7280;}
  .badge-error{background:rgba(253,187,31,.12);color:#B45309;}
  .account-card{background:white;border-radius:16px;border:1.5px solid #e3e9ee;padding:20px;transition:box-shadow .2s,border-color .2s;}
  .account-card:hover{box-shadow:0 8px 32px rgba(2,27,46,.08);border-color:#b4cfe8;}
  .account-card.is-expired{border-color:#fee2e2;background:#fffafa;}
  .connect-card{border:2px dashed #e3e9ee;border-radius:16px;padding:20px;display:flex;align-items:center;gap:14px;cursor:pointer;transition:all .2s;text-decoration:none;color:inherit;}
  .connect-card:hover{border-color:#65a1d8;background:rgba(101,161,216,.04);}
  .expiry-bar{height:3px;border-radius:2px;background:#e3e9ee;overflow:hidden;margin-top:8px;}
  .expiry-fill{height:100%;border-radius:2px;transition:width .5s;}
  .warning-banner{background:linear-gradient(90deg,rgba(255,64,28,.06),rgba(253,187,31,.06));border:1px solid rgba(255,64,28,.2);border-radius:12px;padding:14px 18px;margin-bottom:24px;}
</style>
@endsection

@section('content')
<div class="app-content">

  {{-- Page header --}}
  <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:28px;">
    <div>
      <h1 style="font-size:22px;font-weight:800;color:var(--ink);letter-spacing:-.5px;">Social Accounts</h1>
      <p style="font-size:14px;color:rgba(2,27,46,.5);margin-top:4px;">Connect and manage social media channels for publishing.</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
      <span style="font-size:13px;color:rgba(2,27,46,.5);">
        {{ $accounts->count() }} connected
      </span>
    </div>
  </div>

  {{-- Flash messages --}}
  @if(session('success'))
  <div style="background:rgba(34,176,126,.1);border:1px solid rgba(34,176,126,.3);border-radius:12px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:14px;font-weight:600;">
    ✓ {{ session('success') }}
  </div>
  @endif

  @if($errors->any())
  <div style="background:rgba(255,64,28,.08);border:1px solid rgba(255,64,28,.25);border-radius:12px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:14px;">
    {{ $errors->first() }}
  </div>
  @endif

  {{-- Expiry warnings --}}
  @php $expiring = $accounts->filter(fn ($a) => $a->isExpiringSoon() || $a->isExpired()); @endphp
  @if($expiring->isNotEmpty())
  <div class="warning-banner" style="display:flex;align-items:center;gap:12px;">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" style="flex-shrink:0;color:#FF401C">
      <path d="M10 2L2 17h16L10 2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
      <path d="M10 8v4M10 14h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>
    <div>
      <span style="font-size:13px;font-weight:700;color:#991b1b;">
        {{ $expiring->count() }} account{{ $expiring->count() > 1 ? 's' : '' }} need{{ $expiring->count() === 1 ? 's' : '' }} attention —
      </span>
      <span style="font-size:13px;color:#7f1d1d;">token{{ $expiring->count() > 1 ? 's' : '' }} expired or expiring soon. Reconnect to avoid publishing failures.</span>
    </div>
  </div>
  @endif

  {{-- Connected accounts --}}
  @if($accounts->isNotEmpty())
  <h2 style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:rgba(2,27,46,.4);margin-bottom:14px;">Connected Accounts</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:36px;">
    @foreach($accounts as $account)
    @php
      $expired    = $account->isExpired();
      $expireSoon = $account->isExpiringSoon();
      $platform   = $account->platform;
      $color      = $platform?->color ?? '#6B7280';
    @endphp
    <div class="account-card {{ $expired ? 'is-expired' : '' }}">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
        <div style="display:flex;align-items:center;gap:12px;">
          {{-- Platform icon --}}
          <div class="platform-icon" style="background:{{ $color }};">
            @if($platform?->slug === 'facebook') f
            @elseif($platform?->slug === 'instagram') IG
            @elseif($platform?->slug === 'linkedin') in
            @elseif($platform?->slug === 'twitter') 𝕏
            @elseif($platform?->slug === 'pinterest') P
            @elseif($platform?->slug === 'youtube') ▶
            @elseif($platform?->slug === 'threads') @
            @else {{ substr($platform?->name ?? '?', 0, 1) }}
            @endif
          </div>
          <div>
            <div style="font-size:14px;font-weight:700;color:var(--ink);">{{ $account->account_name }}</div>
            <div style="font-size:12px;color:rgba(2,27,46,.45);">{{ $platform?->name }}</div>
          </div>
        </div>
        {{-- Status badge --}}
        <span class="status-badge {{ $expired ? 'badge-expired' : ($expireSoon ? 'badge-error' : 'badge-active') }}">
          <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
          {{ $expired ? 'Expired' : ($expireSoon ? 'Expiring' : 'Active') }}
        </span>
      </div>

      {{-- Avatar + username --}}
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        @if($account->avatar)
          <img src="{{ $account->avatar }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" onerror="this.style.display='none'">
        @else
          <div style="width:36px;height:36px;border-radius:50%;background:rgba(101,161,216,.15);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#4a8ccc;">{{ substr($account->account_name, 0, 1) }}</div>
        @endif
        <div>
          @if($account->username)
            <div style="font-size:13px;font-weight:600;color:rgba(2,27,46,.7);">@{{ $account->username }}</div>
          @endif
          <div style="font-size:12px;color:rgba(2,27,46,.4);">
            {{ $account->pages_count }} page{{ $account->pages_count !== 1 ? 's' : '' }} ·
            Synced {{ $account->last_synced_at?->diffForHumans() ?? 'never' }}
          </div>
        </div>
      </div>

      {{-- Token expiry progress --}}
      @if($account->token_expires_at)
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
          <span style="font-size:11px;color:rgba(2,27,46,.4);">Token expires</span>
          <span style="font-size:11px;font-weight:600;color:{{ $expired ? '#FF401C' : ($expireSoon ? '#B45309' : '#22B07E') }};">
            {{ $expired ? 'Expired' : $account->token_expires_at->diffForHumans() }}
          </span>
        </div>
        <div class="expiry-bar">
          @php
            $totalLife  = $account->created_at->diffInSeconds($account->token_expires_at);
            $remaining  = max(0, now()->diffInSeconds($account->token_expires_at, false));
            $pct        = $totalLife > 0 ? min(100, round($remaining / $totalLife * 100)) : 0;
            $barColor   = $expired ? '#FF401C' : ($expireSoon ? '#FDBB1F' : '#22B07E');
          @endphp
          <div class="expiry-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
        </div>
      </div>
      @endif

      {{-- Actions --}}
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('social.show', $account->uuid) }}" style="flex:1;text-align:center;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;background:rgba(101,161,216,.1);color:#2f76bd;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='rgba(101,161,216,.2)'" onmouseout="this.style.background='rgba(101,161,216,.1)'">
          View Details
        </a>

        @if($expired || $expireSoon)
        <a href="{{ route('social.connect', $account->platform?->slug) }}" style="flex:1;text-align:center;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;background:#FF401C;color:white;text-decoration:none;">
          Reconnect
        </a>
        @else
        <form method="POST" action="{{ route('social.sync', $account->uuid) }}" style="flex:1;">
          @csrf
          <button type="submit" style="width:100%;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;background:rgba(2,27,46,.06);color:rgba(2,27,46,.7);border:none;cursor:pointer;transition:background .15s;" onmouseover="this.style.background='rgba(2,27,46,.1)'" onmouseout="this.style.background='rgba(2,27,46,.06)'">
            Sync
          </button>
        </form>
        @endif

        <form method="POST" action="{{ route('social.disconnect', $account->uuid) }}"
              onsubmit="return confirm('Disconnect {{ addslashes($account->account_name) }}? Scheduled posts for this account may fail.')"
              style="display:contents;">
          @csrf @method('DELETE')
          <button type="submit" title="Disconnect" style="padding:7px 10px;border-radius:8px;background:rgba(255,64,28,.08);color:#FF401C;border:none;cursor:pointer;font-size:14px;">✕</button>
        </form>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  {{-- Connect new accounts --}}
  <h2 style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:rgba(2,27,46,.4);margin-bottom:14px;">
    {{ $accounts->isEmpty() ? 'Connect Your First Account' : 'Add Another Account' }}
  </h2>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
    @foreach($platforms as $platform)
    @php $isConnected = $connected->has($platform->slug) && $connected[$platform->slug]->isNotEmpty(); @endphp
    <a href="{{ route('social.connect', $platform->slug) }}" class="connect-card">
      <div class="platform-icon" style="background:{{ $platform->color }};">
        @if($platform->slug === 'facebook') f
        @elseif($platform->slug === 'instagram') IG
        @elseif($platform->slug === 'linkedin') in
        @elseif($platform->slug === 'twitter') 𝕏
        @elseif($platform->slug === 'pinterest') P
        @elseif($platform->slug === 'youtube') ▶
        @elseif($platform->slug === 'threads') @
        @else {{ substr($platform->name, 0, 1) }}
        @endif
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:700;color:var(--ink);">{{ $platform->name }}</div>
        @if($isConnected)
          <div style="font-size:11px;color:#22B07E;font-weight:600;">{{ $connected[$platform->slug]->count() }} connected · Add more</div>
        @else
          <div style="font-size:11px;color:rgba(2,27,46,.4);">Click to connect</div>
        @endif
      </div>
      <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="rgba(2,27,46,.3)" stroke-width="2">
        <path d="M10 3a7 7 0 1 1 0 14A7 7 0 0 1 10 3zM10 7v6M7 10h6" stroke-linecap="round"/>
      </svg>
    </a>
    @endforeach
  </div>

  {{-- Platform capabilities legend --}}
  <div style="margin-top:32px;padding:20px;background:white;border-radius:14px;border:1.5px solid #e3e9ee;">
    <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:rgba(2,27,46,.4);margin-bottom:14px;">Platform Capabilities</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
      @foreach($platforms as $platform)
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="platform-icon" style="background:{{ $platform->color }};width:24px;height:24px;font-size:10px;border-radius:6px;">
          @if($platform->slug === 'facebook') f
          @elseif($platform->slug === 'instagram') IG
          @elseif($platform->slug === 'linkedin') in
          @elseif($platform->slug === 'twitter') 𝕏
          @elseif($platform->slug === 'pinterest') P
          @elseif($platform->slug === 'youtube') ▶
          @elseif($platform->slug === 'threads') @
          @else {{ substr($platform->name, 0, 1) }}
          @endif
        </div>
        <div style="font-size:12px;color:rgba(2,27,46,.6);">
          {{ implode(' · ', array_map('ucfirst', $platform->capabilities ?? [])) }}
        </div>
      </div>
      @endforeach
    </div>
  </div>

</div>
@endsection
