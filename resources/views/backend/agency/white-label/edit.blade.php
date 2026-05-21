@extends('layouts.backend')
@section('title', 'White-label Settings')

@section('content')

<div class="max-w-3xl mx-auto">
  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('agency.dashboard') }}" class="text-ink/40 hover:text-ink">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div>
      <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-1">White-label</div>
      <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ $workspace->workspace_name }}</h1>
    </div>
  </div>

  <div x-data="whiteLabelForm()" class="space-y-5">

    {{-- Branding --}}
    <div class="card p-6">
      <h3 class="font-bold mb-4">Brand Identity</h3>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Brand Name</label>
          <input type="text" x-model="form.brand_name"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="My Agency" value="{{ $settings?->brand_name }}">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Support Email</label>
          <input type="email" x-model="form.support_email"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="support@agency.com" value="{{ $settings?->support_email }}">
        </div>
      </div>
    </div>

    {{-- Colors --}}
    <div class="card p-6">
      <h3 class="font-bold mb-4">Brand Colors</h3>
      <div class="grid sm:grid-cols-3 gap-4">
        @foreach([
          ['primary_color',   'Primary',   $settings?->primary_color   ?? '#6366F1'],
          ['secondary_color', 'Secondary', $settings?->secondary_color ?? '#8B5CF6'],
          ['accent_color',    'Accent',    $settings?->accent_color    ?? '#EC4899'],
        ] as [$name,$label,$default])
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">{{ $label }}</label>
          <div class="flex items-center gap-2">
            <input type="color" x-model="form.{{ $name }}" value="{{ $default }}"
                   class="w-10 h-10 rounded-lg border border-line cursor-pointer">
            <input type="text" x-model="form.{{ $name }}"
                   class="flex-1 border border-line rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:border-brand-400"
                   value="{{ $default }}">
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Custom Domain --}}
    <div class="card p-6">
      <h3 class="font-bold mb-4">Custom Domain</h3>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-ink/50 mb-1.5">Domain</label>
          <input type="text" x-model="form.custom_domain"
                 class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-400"
                 placeholder="dashboard.youragency.com" value="{{ $settings?->custom_domain }}">
        </div>
        <div class="flex items-end">
          <button @click="verifyDomain()"
                  :disabled="!form.custom_domain || verifying"
                  class="w-full px-4 py-2.5 text-sm font-semibold border border-line rounded-xl hover:bg-paper disabled:opacity-40">
            <span x-text="verifying ? 'Verifying…' : 'Verify DNS'"></span>
          </button>
        </div>
      </div>
      @if($settings?->custom_domain)
      <div class="mt-3 text-xs text-ink/50 bg-paper rounded-xl p-3 font-mono">
        Add this TXT record to your DNS:<br>
        <strong>schedara-verify={{ $workspace->id }}</strong>
      </div>
      @if($settings->domain_verified)
        <div class="mt-2 flex items-center gap-1.5 text-sm text-mint font-semibold">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Domain verified
        </div>
      @else
        <div class="mt-2 text-xs text-amber-600 font-medium">⏳ Domain not yet verified</div>
      @endif
      @endif
    </div>

    {{-- Options --}}
    <div class="card p-6">
      <h3 class="font-bold mb-4">Options</h3>
      <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" x-model="form.hide_saas_branding"
               {{ $settings?->hide_saas_branding ? 'checked' : '' }}
               class="w-4 h-4 rounded border-line">
        <div>
          <div class="text-sm font-semibold">Hide SaaS Branding</div>
          <div class="text-xs text-ink/40">Remove "Powered by Schedara" from client-facing pages</div>
        </div>
      </label>
    </div>

    {{-- Error/success --}}
    <div x-show="message" x-text="message"
         :class="error ? 'bg-red-50 text-red-600 border-red-200' : 'bg-mint/10 text-mint border-mint/20'"
         class="text-sm px-4 py-3 rounded-xl border" style="display:none"></div>

    <div class="flex justify-end gap-3">
      <a href="{{ route('agency.dashboard') }}" class="px-5 py-2.5 text-sm font-semibold text-ink/50 hover:text-ink">Cancel</a>
      <button @click="save()" :disabled="saving" class="btn-primary">
        <span x-text="saving ? 'Saving…' : 'Save Settings'"></span>
      </button>
    </div>
  </div>
</div>

<script>
function whiteLabelForm() {
  return {
    form: {
      brand_name:         '{{ $settings?->brand_name }}',
      support_email:      '{{ $settings?->support_email }}',
      primary_color:      '{{ $settings?->primary_color ?? "#6366F1" }}',
      secondary_color:    '{{ $settings?->secondary_color ?? "#8B5CF6" }}',
      accent_color:       '{{ $settings?->accent_color ?? "#EC4899" }}',
      custom_domain:      '{{ $settings?->custom_domain }}',
      hide_saas_branding: {{ $settings?->hide_saas_branding ? 'true' : 'false' }},
    },
    saving: false,
    verifying: false,
    message: null,
    error: false,

    async save() {
      this.saving = true;
      this.message = null;
      try {
        const res = await fetch('/api/workspaces/{{ $workspace->uuid }}/white-label', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
          body: JSON.stringify(this.form),
        });
        const json = await res.json();
        this.error = !res.ok;
        this.message = json.message || (res.ok ? 'Settings saved.' : 'Error saving.');
      } finally {
        this.saving = false;
      }
    },

    async verifyDomain() {
      this.verifying = true;
      this.message = null;
      try {
        const res = await fetch('/api/workspaces/{{ $workspace->uuid }}/white-label/verify-domain', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
        });
        const json = await res.json();
        this.error = !json.verified;
        this.message = json.message;
        if (json.verified) location.reload();
      } finally {
        this.verifying = false;
      }
    },
  };
}
</script>
@endsection
