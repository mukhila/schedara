@extends('layouts.backend')

@section('title', 'Slack Integration')

@section('content')
<div class="max-w-2xl mx-auto">

  <div class="mb-6">
    <h1 class="text-xl font-bold text-ink">Slack Integration</h1>
    <p class="text-sm text-ink/50 mt-0.5">Receive workspace alerts in your Slack channel</p>
  </div>

  @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(34,176,126,.1);color:#22B07E">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(255,64,28,.1);color:#FF401C">{{ session('error') }}</div>
  @endif

  {{-- Status card --}}
  <div class="card p-6 mb-6">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#4A154B">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="#fff"><path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/></svg>
      </div>
      <div class="flex-1">
        <div class="font-bold text-ink">Slack</div>
        @if($integration && !$integration->trashed() && $integration->isActive())
          <div class="text-sm text-ink/60 mt-0.5">Connected to <strong>{{ $integration->channel_name }}</strong>
            @if($integration->workspace_name)
              in workspace <strong>{{ $integration->workspace_name }}</strong>
            @endif
          </div>
        @else
          <div class="text-sm text-ink/60 mt-0.5">Not connected</div>
        @endif
      </div>
      <span class="pill {{ ($integration && !$integration->trashed() && $integration->isActive()) ? 'pill-mint' : 'pill-coral' }} pill-dot">
        {{ ($integration && !$integration->trashed() && $integration->isActive()) ? 'Connected' : 'Disconnected' }}
      </span>
    </div>
  </div>

  {{-- Connect form --}}
  <div class="card p-6 mb-4">
    <h2 class="font-bold text-sm text-ink mb-4">{{ ($integration && $integration->isActive()) ? 'Update Connection' : 'Connect Slack' }}</h2>

    <form method="POST" action="{{ route('notifications.slack.connect') }}" class="space-y-4">
      @csrf

      <div>
        <label class="text-xs font-bold text-ink/50 uppercase tracking-widest block mb-1.5">Slack Webhook URL</label>
        <input type="url" name="webhook_url" required
               value="{{ old('webhook_url', $integration?->webhook_url) }}"
               placeholder="https://hooks.slack.com/services/T.../B.../..."
               class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-brand-400/30"
               style="border-color:var(--line)">
        <p class="text-xs text-ink/40 mt-1">Create an incoming webhook in your <a href="https://api.slack.com/apps" target="_blank" class="underline">Slack App settings</a></p>
      </div>

      <div>
        <label class="text-xs font-bold text-ink/50 uppercase tracking-widest block mb-1.5">Channel Name</label>
        <input type="text" name="channel_name" required
               value="{{ old('channel_name', $integration?->channel_name ?? '#general') }}"
               placeholder="#general"
               class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-brand-400/30"
               style="border-color:var(--line)">
      </div>

      <div>
        <label class="text-xs font-bold text-ink/50 uppercase tracking-widest block mb-1.5">Workspace Name <span class="font-normal normal-case text-ink/40">(optional)</span></label>
        <input type="text" name="workspace_name"
               value="{{ old('workspace_name', $integration?->workspace_name) }}"
               placeholder="My Company"
               class="w-full px-3 py-2.5 text-sm border rounded-lg outline-none focus:ring-2 focus:ring-brand-400/30"
               style="border-color:var(--line)">
      </div>

      <button type="submit"
              class="px-5 py-2.5 bg-ink text-white text-sm font-bold rounded-lg hover:bg-brand-800 transition-colors">
        {{ ($integration && $integration->isActive()) ? 'Update' : 'Connect' }}
      </button>
    </form>
  </div>

  {{-- Test & Disconnect --}}
  @if($integration && !$integration->trashed() && $integration->isActive())
  <div class="flex gap-3">
    <form method="POST" action="{{ route('notifications.slack.test') }}">
      @csrf
      <button type="submit" class="px-4 py-2 text-sm font-bold rounded-lg border transition-colors hover:bg-paper" style="border-color:var(--line)">
        Send Test Message
      </button>
    </form>
    <form method="POST" action="{{ route('notifications.slack.disconnect') }}">
      @csrf
      @method('DELETE')
      <button type="submit"
              onclick="return confirm('Disconnect Slack integration?')"
              class="px-4 py-2 text-sm font-bold rounded-lg text-coral border border-coral/30 hover:bg-coral/5 transition-colors">
        Disconnect
      </button>
    </form>
  </div>
  @endif

  {{-- How it works --}}
  <div class="card p-5 mt-6">
    <div class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-3">What gets sent to Slack?</div>
    <div class="grid grid-cols-2 gap-2">
      @foreach(['Post published', 'Post failed', 'Billing alert', 'AI content generated', 'Campaign completed', 'Team invitation'] as $item)
      <div class="flex items-center gap-2 text-xs text-ink/60">
        <svg class="w-3 h-3 text-mint flex-shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        {{ $item }}
      </div>
      @endforeach
    </div>
  </div>

</div>
@endsection
