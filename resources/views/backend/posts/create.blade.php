@extends('layouts.backend')

@section('title', 'New Post')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  {{-- Header --}}
  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('posts.index') }}" class="text-ink/40 hover:text-ink transition-colors">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <h1 class="text-2xl font-bold text-ink">New Post</h1>
  </div>

  <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" id="post-form">
    @csrf

    @if($errors->any())
      <div class="mb-6 p-4 rounded-xl bg-coral/10 border border-coral/20 text-coral text-sm">
        <ul class="list-disc pl-4 space-y-1">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      {{-- Main editor --}}
      <div class="lg:col-span-2 space-y-4">

        {{-- Post type --}}
        <div class="bg-white rounded-2xl border border-line p-5">
          <label class="block text-sm font-semibold text-ink/70 mb-3">Post type</label>
          <div class="flex flex-wrap gap-2">
            @foreach(['text','image','video','carousel','reel','shorts'] as $type)
              <label class="cursor-pointer">
                <input type="radio" name="type" value="{{ $type }}" class="sr-only" @checked(old('type', 'text') === $type)>
                <span class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-all
                  {{ old('type','text') === $type ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-line text-ink/60 hover:border-brand-300' }}">
                  {{ ucfirst($type) }}
                </span>
              </label>
            @endforeach
          </div>
        </div>

        {{-- Content --}}
        <div class="bg-white rounded-2xl border border-line p-5">
          <label class="block text-sm font-semibold text-ink/70 mb-2">Content <span class="text-coral">*</span></label>
          <textarea name="content" rows="6" required
                    placeholder="Write your post content…"
                    class="w-full text-sm rounded-xl border border-line p-3 resize-none focus:outline-none focus:ring-2 focus:ring-brand-400/30 @error('content') border-coral @enderror"
          >{{ old('content') }}</textarea>
          @error('content')
            <p class="text-xs text-coral mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Caption --}}
        <div class="bg-white rounded-2xl border border-line p-5">
          <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-semibold text-ink/70">Caption (optional)</label>
            <button type="button" id="ai-caption-btn"
                    class="text-xs px-2.5 py-1 rounded-lg bg-purple-50 text-purple-600 font-medium hover:bg-purple-100 transition-colors flex items-center gap-1">
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
              AI Generate
            </button>
          </div>
          <textarea name="caption" rows="3"
                    placeholder="Optional platform caption (Instagram-style)…"
                    class="w-full text-sm rounded-xl border border-line p-3 resize-none focus:outline-none focus:ring-2 focus:ring-brand-400/30"
          >{{ old('caption') }}</textarea>
        </div>

        {{-- Hashtags --}}
        <div class="bg-white rounded-2xl border border-line p-5">
          <label class="block text-sm font-semibold text-ink/70 mb-2">Hashtags</label>
          <div id="hashtag-container" class="flex flex-wrap gap-2 mb-3">
            @foreach(old('hashtags', []) as $i => $tag)
              <span class="hashtag-chip flex items-center gap-1 px-2.5 py-1 bg-brand-50 text-brand-700 rounded-full text-sm font-medium">
                #{{ $tag }}
                <button type="button" onclick="removeHashtag(this)" class="text-brand-400 hover:text-brand-700">×</button>
                <input type="hidden" name="hashtags[]" value="{{ $tag }}">
              </span>
            @endforeach
          </div>
          <div class="flex gap-2">
            <input type="text" id="hashtag-input" placeholder="Add hashtag…" class="flex-1 text-sm rounded-lg border border-line px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-400/30">
            <button type="button" onclick="addHashtag()" class="px-3 py-1.5 text-sm rounded-lg bg-brand-50 text-brand-700 font-medium hover:bg-brand-100 transition-colors">Add</button>
          </div>
        </div>

        {{-- Title (optional) --}}
        <div class="bg-white rounded-2xl border border-line p-5">
          <label class="block text-sm font-semibold text-ink/70 mb-2">Internal title (optional)</label>
          <input type="text" name="title" value="{{ old('title') }}" placeholder="For your reference only"
                 class="w-full text-sm rounded-xl border border-line px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-400/30">
        </div>

      </div>

      {{-- Sidebar --}}
      <div class="space-y-4">

        {{-- Platform selection --}}
        <div class="bg-white rounded-2xl border border-line p-5">
          <label class="block text-sm font-semibold text-ink/70 mb-3">Platforms <span class="text-coral">*</span></label>
          @error('platforms')
            <p class="text-xs text-coral mb-2">{{ $message }}</p>
          @enderror

          @php
            $platformColors = ['facebook'=>'#1877F2','instagram'=>'#E1306C','twitter'=>'#1DA1F2','linkedin'=>'#0A66C2','pinterest'=>'#E60023','youtube'=>'#FF0000','threads'=>'#555'];
            $selectedPlatforms = old('platforms', []);
          @endphp

          <div class="space-y-2">
            @foreach(['facebook','instagram','twitter','linkedin','pinterest','youtube','threads'] as $platform)
              @php $c = $platformColors[$platform] ?? '#888'; @endphp
              <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-paper cursor-pointer">
                <input type="checkbox" name="platforms[]" value="{{ $platform }}"
                       class="rounded border-line"
                       @checked(in_array($platform, $selectedPlatforms))
                       onchange="togglePlatformAccount('{{ $platform }}', this.checked)">
                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background:{{ $c }}">
                  {{ strtoupper($platform[0]) }}
                </span>
                <span class="text-sm font-medium text-ink">{{ ucfirst($platform) }}</span>
              </label>
            @endforeach
          </div>
        </div>

        {{-- Account picker (per platform) --}}
        @foreach(['facebook','instagram','twitter','linkedin','pinterest','youtube','threads'] as $platform)
          @php
            $platformAccounts = $accounts->filter(fn($a) => $a->platform->slug === $platform || ($platform === 'instagram' && $a->platform->slug === 'facebook'));
          @endphp
          <div id="account-{{ $platform }}" class="hidden bg-white rounded-2xl border border-line p-5">
            <label class="block text-sm font-semibold text-ink/70 mb-2">
              {{ ucfirst($platform) }} Account
            </label>
            @if($platformAccounts->isEmpty())
              <p class="text-xs text-ink/50">
                No connected account.
                <a href="{{ route('social.connect', $platform) }}" class="text-brand-600 hover:underline">Connect one</a>
              </p>
            @else
              <select name="platform_accounts[{{ $platform }}]" class="w-full text-sm rounded-lg border border-line px-3 py-2 focus:outline-none">
                <option value="">Select account…</option>
                @foreach($platformAccounts as $acc)
                  <option value="{{ $acc->uuid }}" @selected(old("platform_accounts.{$platform}") === $acc->uuid)>
                    {{ $acc->account_name }}
                  </option>
                @endforeach
              </select>
            @endif
          </div>
        @endforeach

        {{-- Schedule --}}
        <div class="bg-white rounded-2xl border border-line p-5">
          <label class="block text-sm font-semibold text-ink/70 mb-3">Schedule</label>

          <div class="space-y-2 mb-3">
            @foreach(['draft' => 'Save as draft', 'scheduled' => 'Schedule for later', 'queued' => 'Add to queue'] as $val => $label)
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="status" value="{{ $val }}" @checked(old('status','draft') === $val) class="border-line" onchange="toggleScheduleAt('{{ $val }}')">
                <span class="text-sm text-ink">{{ $label }}</span>
              </label>
            @endforeach
          </div>

          <div id="schedule-at-block" class="{{ old('status') === 'scheduled' ? '' : 'hidden' }}">
            <label class="block text-xs text-ink/50 mb-1">Date &amp; time</label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                   class="w-full text-sm rounded-lg border border-line px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-400/30">

            <label class="block text-xs text-ink/50 mb-1 mt-3">Timezone</label>
            <select name="timezone" class="w-full text-sm rounded-lg border border-line px-3 py-2 focus:outline-none">
              @foreach(timezone_identifiers_list() as $tz)
                <option value="{{ $tz }}" @selected(old('timezone', 'UTC') === $tz)>{{ $tz }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Evergreen / Repost --}}
        <div class="bg-white rounded-2xl border border-line p-5">
          <label class="block text-sm font-semibold text-ink/70 mb-3">Evergreen Content</label>

          <label class="flex items-center gap-2 cursor-pointer mb-3">
            <input type="checkbox" name="is_evergreen" value="1" @checked(old('is_evergreen'))
                   onchange="document.getElementById('repost-block').classList.toggle('hidden', !this.checked)"
                   class="rounded border-line">
            <span class="text-sm text-ink">Mark as evergreen</span>
          </label>

          <div id="repost-block" class="{{ old('is_evergreen') ? '' : 'hidden' }}">
            <label class="flex items-center gap-2 cursor-pointer mb-2">
              <input type="checkbox" name="auto_repost" value="1" @checked(old('auto_repost')) class="rounded border-line">
              <span class="text-sm text-ink">Auto-repost</span>
            </label>
            <div>
              <label class="block text-xs text-ink/50 mb-1">Repost every (days)</label>
              <input type="number" name="repost_frequency" value="{{ old('repost_frequency', 30) }}" min="1" max="365"
                     class="w-full text-sm rounded-lg border border-line px-3 py-2 focus:outline-none">
            </div>
          </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90" style="background:#4a8ccc">
          Create Post
        </button>

      </div>
    </div>
  </form>
</div>

<script>
function addHashtag() {
  const input = document.getElementById('hashtag-input');
  let tag = input.value.trim().replace(/^#/, '').toLowerCase();
  if (!tag) return;

  const container = document.getElementById('hashtag-container');
  const chip = document.createElement('span');
  chip.className = 'hashtag-chip flex items-center gap-1 px-2.5 py-1 bg-brand-50 text-brand-700 rounded-full text-sm font-medium';
  chip.innerHTML = `#${tag} <button type="button" onclick="removeHashtag(this)" class="text-brand-400 hover:text-brand-700">×</button><input type="hidden" name="hashtags[]" value="${tag}">`;
  container.appendChild(chip);
  input.value = '';
}

function removeHashtag(btn) {
  btn.closest('.hashtag-chip').remove();
}

document.getElementById('hashtag-input').addEventListener('keydown', e => {
  if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addHashtag(); }
});

function toggleScheduleAt(val) {
  document.getElementById('schedule-at-block').classList.toggle('hidden', val !== 'scheduled');
}

function togglePlatformAccount(platform, show) {
  document.getElementById('account-' + platform)?.classList.toggle('hidden', !show);
}

// Init: show account pickers for pre-checked platforms
document.querySelectorAll('input[name="platforms[]"]:checked').forEach(cb => {
  togglePlatformAccount(cb.value, true);
});

// Post type radio style
document.querySelectorAll('input[name="type"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('input[name="type"]').forEach(r => {
      const label = r.nextElementSibling;
      if (r.checked) {
        label.classList.remove('border-line','text-ink/60');
        label.classList.add('border-brand-500','bg-brand-50','text-brand-700');
      } else {
        label.classList.remove('border-brand-500','bg-brand-50','text-brand-700');
        label.classList.add('border-line','text-ink/60');
      }
    });
  });
});
</script>
@endsection
