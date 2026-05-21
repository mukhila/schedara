@extends('layouts.backend')

@section('title', 'Pages — ' . $account->account_name)

@section('head')
<style>
  .page-row{display:flex;align-items:center;gap:14px;padding:14px;border-radius:12px;border:1.5px solid #e3e9ee;margin-bottom:10px;transition:border-color .15s,box-shadow .15s;}
  .page-row:hover{border-color:#b4cfe8;box-shadow:0 2px 12px rgba(2,27,46,.06);}
  .page-row.is-selected{border-color:rgba(34,176,126,.3);background:rgba(34,176,126,.03);}
</style>
@endsection

@section('content')
<div class="app-content">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;">
    <a href="{{ route('social.index') }}" style="color:rgba(2,27,46,.5);text-decoration:none;font-weight:600;">Social Accounts</a>
    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="rgba(2,27,46,.3)" stroke-width="2"><path d="M7 4l6 6-6 6"/></svg>
    <a href="{{ route('social.show', $account->uuid) }}" style="color:rgba(2,27,46,.5);text-decoration:none;font-weight:600;">{{ $account->account_name }}</a>
    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="rgba(2,27,46,.3)" stroke-width="2"><path d="M7 4l6 6-6 6"/></svg>
    <span style="color:var(--ink);font-weight:700;">Pages</span>
  </div>

  {{-- Flash --}}
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

  {{-- Account summary bar --}}
  <div class="card p-5" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:14px;">
      <div style="width:44px;height:44px;border-radius:12px;background:{{ $account->platform?->color ?? '#6B7280' }};display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:900;color:white;flex-shrink:0;">
        @if($account->platform?->slug === 'facebook') f
        @elseif($account->platform?->slug === 'instagram') IG
        @elseif($account->platform?->slug === 'linkedin') in
        @elseif($account->platform?->slug === 'twitter') 𝕏
        @elseif($account->platform?->slug === 'pinterest') P
        @elseif($account->platform?->slug === 'youtube') ▶
        @elseif($account->platform?->slug === 'threads') @
        @elseif($account->platform?->slug === 'tiktok') TT
        @else {{ substr($account->platform?->name ?? '?', 0, 1) }}
        @endif
      </div>
      <div>
        <div style="font-size:15px;font-weight:800;color:var(--ink);">{{ $account->account_name }}</div>
        <div style="font-size:12px;color:rgba(2,27,46,.45);">
          {{ $account->platform?->name }} ·
          {{ $pages->count() }} page{{ $pages->count() !== 1 ? 's' : '' }} ·
          {{ $pages->where('is_selected', true)->count() }} selected
        </div>
      </div>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
      <a href="{{ route('social.show', $account->uuid) }}"
         style="padding:8px 16px;border-radius:10px;background:rgba(2,27,46,.06);color:rgba(2,27,46,.6);font-size:13px;font-weight:700;text-decoration:none;">
        ← Back to Account
      </a>
      <form method="POST" action="{{ route('social.sync', $account->uuid) }}">
        @csrf
        <button type="submit"
                style="padding:8px 18px;border-radius:10px;background:rgba(101,161,216,.1);color:#2f76bd;font-size:13px;font-weight:700;border:none;cursor:pointer;">
          Sync Pages
        </button>
      </form>
    </div>
  </div>

  {{-- Pages list --}}
  <div class="card p-6">
    <div style="margin-bottom:20px;">
      <h2 style="font-size:16px;font-weight:800;color:var(--ink);">Pages / Channels / Boards</h2>
      <p style="font-size:13px;color:rgba(2,27,46,.5);margin-top:4px;">
        Select which pages to use when publishing posts to {{ $account->platform?->name }}.
      </p>
    </div>

    @forelse($pages as $page)
    <div class="page-row {{ $page->is_selected ? 'is-selected' : '' }}">

      {{-- Avatar --}}
      @if($page->avatar)
        <img src="{{ $page->avatar }}"
             style="width:44px;height:44px;border-radius:10px;object-fit:cover;flex-shrink:0;"
             onerror="this.style.display='none'">
      @else
        <div style="width:44px;height:44px;border-radius:10px;background:rgba(101,161,216,.12);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:#4a8ccc;flex-shrink:0;">
          {{ substr($page->page_name, 0, 1) }}
        </div>
      @endif

      {{-- Info --}}
      <div style="flex:1;min-width:0;">
        <div style="font-size:14px;font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          {{ $page->page_name }}
        </div>
        <div style="font-size:12px;color:rgba(2,27,46,.45);margin-top:3px;">
          @if($page->category){{ $page->category }} · @endif
          {{ ucfirst($page->page_type) }} ·
          {{ number_format($page->followers_count) }} followers
        </div>
      </div>

      {{-- Toggle --}}
      <form method="POST" action="{{ route('social.pages.toggle', [$account->uuid, $page->uuid]) }}"
            style="flex-shrink:0;">
        @csrf
        <button type="submit"
                style="padding:7px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;transition:all .15s;
                       {{ $page->is_selected
                          ? 'background:rgba(34,176,126,.12);color:#22B07E;'
                          : 'background:rgba(2,27,46,.06);color:rgba(2,27,46,.5);' }}">
          {{ $page->is_selected ? '✓ Selected' : 'Select' }}
        </button>
      </form>
    </div>
    @empty
    <div style="text-align:center;padding:48px 0;color:rgba(2,27,46,.35);">
      <div style="font-size:36px;margin-bottom:12px;">📋</div>
      <div style="font-size:15px;font-weight:700;margin-bottom:8px;">No pages found</div>
      <div style="font-size:13px;max-width:340px;margin:0 auto;line-height:1.6;">
        Click <strong>Sync Pages</strong> above to fetch the latest pages, channels, or boards
        linked to this {{ $account->platform?->name }} account.
      </div>
    </div>
    @endforelse
  </div>

</div>
@endsection
