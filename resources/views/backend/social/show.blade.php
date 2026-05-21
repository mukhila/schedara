@extends('layouts.backend')

@section('title', $account->account_name . ' — Social Account')

@section('head')
<style>
  .detail-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f0f4f7;}
  .detail-row:last-child{border-bottom:none;}
  .detail-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:rgba(2,27,46,.4);}
  .detail-value{font-size:14px;font-weight:600;color:var(--ink);}
  .page-row{display:flex;align-items:center;gap:12px;padding:14px;border-radius:12px;border:1.5px solid #e3e9ee;margin-bottom:10px;transition:border-color .15s;}
  .page-row:hover{border-color:#b4cfe8;}
  .log-row{display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#f8fbfc;margin-bottom:8px;}
  .log-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:5px;}
  .log-success{background:#22B07E;}
  .log-failure{background:#FF401C;}
  .log-pending{background:#FDBB1F;}
</style>
@endsection

@section('content')
<div class="app-content">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;">
    <a href="{{ route('social.index') }}" style="color:rgba(2,27,46,.5);text-decoration:none;font-weight:600;">Social Accounts</a>
    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="rgba(2,27,46,.3)" stroke-width="2"><path d="M7 4l6 6-6 6"/></svg>
    <span style="color:var(--ink);font-weight:700;">{{ $account->account_name }}</span>
  </div>

  @if(session('success'))
  <div style="background:rgba(34,176,126,.1);border:1px solid rgba(34,176,126,.3);border-radius:12px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:14px;font-weight:600;">
    ✓ {{ session('success') }}
  </div>
  @endif

  <div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">

    {{-- Left column: account card --}}
    <div>
      <div class="card p-6" style="margin-bottom:16px;">
        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #f0f4f7;">
          <div style="width:48px;height:48px;border-radius:12px;background:{{ $account->platform?->color ?? '#6B7280' }};display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:white;flex-shrink:0;">
            @if($account->platform?->slug === 'facebook') f
            @elseif($account->platform?->slug === 'instagram') IG
            @elseif($account->platform?->slug === 'linkedin') in
            @elseif($account->platform?->slug === 'twitter') 𝕏
            @elseif($account->platform?->slug === 'pinterest') P
            @elseif($account->platform?->slug === 'youtube') ▶
            @elseif($account->platform?->slug === 'threads') @
            @else {{ substr($account->platform?->name ?? '?', 0, 1) }}
            @endif
          </div>
          <div>
            <div style="font-size:16px;font-weight:800;color:var(--ink);">{{ $account->account_name }}</div>
            <div style="font-size:12px;color:rgba(2,27,46,.45);margin-top:2px;">{{ $account->platform?->name }}</div>
          </div>
        </div>

        {{-- Avatar + username --}}
        @if($account->avatar || $account->username)
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
          @if($account->avatar)
            <img src="{{ $account->avatar }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #e3e9ee;" onerror="this.style.display='none'">
          @else
            <div style="width:48px;height:48px;border-radius:50%;background:rgba(101,161,216,.15);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#4a8ccc;">{{ substr($account->account_name, 0, 1) }}</div>
          @endif
          <div>
            @if($account->username)<div style="font-size:13px;font-weight:700;color:var(--ink);">@{{ $account->username }}</div>@endif
            @if($account->email)<div style="font-size:12px;color:rgba(2,27,46,.5);">{{ $account->email }}</div>@endif
          </div>
        </div>
        @endif

        {{-- Details --}}
        <div>
          <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value" style="color:{{ $account->status === 'active' ? '#22B07E' : '#FF401C' }};">
              {{ ucfirst($account->status) }}{{ $account->isExpired() ? ' (Expired)' : '' }}
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Platform ID</span>
            <span class="detail-value" style="font-size:12px;font-family:monospace;color:rgba(2,27,46,.6);">{{ $account->platform_user_id }}</span>
          </div>
          @if($account->token_expires_at)
          <div class="detail-row">
            <span class="detail-label">Token Expires</span>
            <span class="detail-value" style="color:{{ $account->isExpired() ? '#FF401C' : ($account->isExpiringSoon() ? '#B45309' : 'inherit') }};">
              {{ $account->token_expires_at->format('M d, Y H:i') }}
              <span style="font-size:11px;font-weight:600;"> ({{ $account->token_expires_at->diffForHumans() }})</span>
            </span>
          </div>
          @endif
          <div class="detail-row">
            <span class="detail-label">Last Synced</span>
            <span class="detail-value">{{ $account->last_synced_at?->diffForHumans() ?? 'Never' }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Connected</span>
            <span class="detail-value">{{ $account->created_at->format('M d, Y') }}</span>
          </div>
          @if($account->scopes)
          <div class="detail-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
            <span class="detail-label">Granted Scopes</span>
            <div style="display:flex;flex-wrap:wrap;gap:5px;">
              @foreach($account->scopes as $scope)
              <span style="background:rgba(101,161,216,.1);color:#2f76bd;font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;">{{ $scope }}</span>
              @endforeach
            </div>
          </div>
          @endif
        </div>
      </div>

      {{-- Actions --}}
      <div style="display:flex;flex-direction:column;gap:8px;">
        @if($account->isExpired())
        <a href="{{ route('social.connect', $account->platform?->slug) }}"
           style="display:block;text-align:center;padding:10px 20px;border-radius:10px;background:#FF401C;color:white;font-size:13px;font-weight:700;text-decoration:none;">
          Reconnect Account
        </a>
        @else
        <form method="POST" action="{{ route('social.sync', $account->uuid) }}">
          @csrf
          <button type="submit" style="width:100%;padding:10px 20px;border-radius:10px;background:rgba(101,161,216,.1);color:#2f76bd;font-size:13px;font-weight:700;border:none;cursor:pointer;">
            Sync Now
          </button>
        </form>
        <form method="POST" action="{{ route('social.refresh', $account->uuid) }}">
          @csrf
          <button type="submit" style="width:100%;padding:10px 20px;border-radius:10px;background:rgba(2,27,46,.06);color:rgba(2,27,46,.7);font-size:13px;font-weight:700;border:none;cursor:pointer;">
            Refresh Token
          </button>
        </form>
        @endif
        <form method="POST" action="{{ route('social.disconnect', $account->uuid) }}"
              onsubmit="return confirm('Disconnect {{ addslashes($account->account_name) }}? This cannot be undone.')">
          @csrf @method('DELETE')
          <button type="submit" style="width:100%;padding:10px 20px;border-radius:10px;background:rgba(255,64,28,.08);color:#FF401C;font-size:13px;font-weight:700;border:none;cursor:pointer;">
            Disconnect
          </button>
        </form>
      </div>
    </div>

    {{-- Right column: pages + logs --}}
    <div>

      {{-- Pages / Channels / Boards --}}
      @if($account->pages->isNotEmpty())
      <div class="card p-6" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
          <h3 style="font-size:15px;font-weight:800;color:var(--ink);">
            Pages / Channels
            <span style="font-size:12px;font-weight:600;color:rgba(2,27,46,.4);margin-left:6px;">({{ $account->pages->count() }})</span>
          </h3>
          <form method="POST" action="{{ route('social.sync', $account->uuid) }}" style="display:inline;">
            @csrf
            <button type="submit" style="font-size:12px;font-weight:700;color:#2f76bd;background:none;border:none;cursor:pointer;">Sync Pages</button>
          </form>
        </div>

        @foreach($account->pages as $page)
        <div class="page-row">
          @if($page->avatar)
            <img src="{{ $page->avatar }}" style="width:40px;height:40px;border-radius:8px;object-fit:cover;" onerror="this.style.display='none'">
          @else
            <div style="width:40px;height:40px;border-radius:8px;background:rgba(101,161,216,.1);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#4a8ccc;flex-shrink:0;">{{ substr($page->page_name, 0, 1) }}</div>
          @endif
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $page->page_name }}</div>
            <div style="font-size:11px;color:rgba(2,27,46,.45);margin-top:2px;">
              {{ $page->category ?? ucfirst($page->page_type) }} ·
              {{ number_format($page->followers_count) }} followers
            </div>
          </div>
          <form method="POST" action="{{ route('social.pages.toggle', [$account->uuid, $page->uuid]) }}">
            @csrf
            <button type="submit" title="{{ $page->is_selected ? 'Deselect' : 'Select for publishing' }}"
                    style="padding:5px 12px;border-radius:8px;font-size:11px;font-weight:700;border:none;cursor:pointer;
                           background:{{ $page->is_selected ? 'rgba(34,176,126,.12)' : 'rgba(2,27,46,.06)' }};
                           color:{{ $page->is_selected ? '#22B07E' : 'rgba(2,27,46,.5)' }};">
              {{ $page->is_selected ? '✓ Selected' : 'Select' }}
            </button>
          </form>
        </div>
        @endforeach
      </div>
      @endif

      {{-- Activity log --}}
      @if($account->logs->isNotEmpty())
      <div class="card p-6">
        <h3 style="font-size:15px;font-weight:800;color:var(--ink);margin-bottom:16px;">Activity Log</h3>
        @foreach($account->logs->take(20) as $log)
        <div class="log-row">
          <div class="log-dot log-{{ $log->status }}"></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:12px;font-weight:700;color:var(--ink);text-transform:capitalize;">{{ str_replace('_', ' ', $log->action) }}</div>
            @if($log->error_message)
              <div style="font-size:11px;color:#FF401C;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $log->error_message }}</div>
            @endif
          </div>
          <div style="font-size:11px;color:rgba(2,27,46,.4);flex-shrink:0;margin-left:12px;">{{ $log->created_at->diffForHumans() }}</div>
        </div>
        @endforeach
      </div>
      @endif

      {{-- No pages yet --}}
      @if($account->pages->isEmpty() && $account->logs->isEmpty())
      <div class="card p-6" style="text-align:center;color:rgba(2,27,46,.4);">
        <div style="font-size:32px;margin-bottom:10px;">📋</div>
        <div style="font-size:14px;font-weight:600;">No pages or activity yet.</div>
        <div style="font-size:13px;margin-top:6px;">Click "Sync Now" to fetch pages and channels from this account.</div>
      </div>
      @endif

    </div>
  </div>

</div>
@endsection
