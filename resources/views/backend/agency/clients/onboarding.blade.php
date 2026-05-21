@extends('layouts.backend')
@section('title', 'Client Onboarding — '.$client->client_name)

@section('content')

<div class="max-w-3xl mx-auto">
  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('agency.clients.show', $client->uuid) }}" class="text-ink/40 hover:text-ink">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div>
      <div class="text-xs font-bold uppercase tracking-[2px] text-brand-600 mb-1">Onboarding</div>
      <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ $client->client_name }}</h1>
    </div>
  </div>

  {{-- Progress --}}
  <div class="card p-5 mb-6">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-semibold">Overall Progress</span>
      <span class="text-2xl font-extrabold text-brand-600">{{ $progress }}%</span>
    </div>
    <div class="h-3 bg-line rounded-full overflow-hidden">
      <div class="h-full rounded-full transition-all duration-500"
           style="width:{{ $progress }}%;background:linear-gradient(90deg,#65a1d8,#22B07E)"></div>
    </div>
  </div>

  {{-- Steps --}}
  <div x-data="onboarding('{{ $client->uuid }}')" class="space-y-4">
    @foreach($steps as $step)
    @php
      $stepDefs = [
        'profile'  => ['icon'=>'👤','label'=>'Client Profile','desc'=>'Basic client information'],
        'branding' => ['icon'=>'🎨','label'=>'Branding Setup','desc'=>'Logo, colors, brand identity'],
        'social'   => ['icon'=>'📱','label'=>'Social Integrations','desc'=>'Connect social media accounts'],
        'team'     => ['icon'=>'👥','label'=>'Team Invitations','desc'=>'Invite team members'],
        'content'  => ['icon'=>'📝','label'=>'Content Setup','desc'=>'Content calendar and templates'],
        'billing'  => ['icon'=>'💳','label'=>'Billing Setup','desc'=>'Subscription and payment'],
      ];
      $def = $stepDefs[$step->onboarding_step] ?? ['icon'=>'⚡','label'=>ucfirst($step->onboarding_step),'desc'=>''];
    @endphp
    <div class="card p-5">
      <div class="flex items-center gap-4">
        <div class="text-2xl w-12 h-12 flex items-center justify-center rounded-xl flex-shrink-0
                    {{ $step->isCompleted() ? 'bg-mint/10' : ($step->status === 'in_progress' ? 'bg-gold/10' : 'bg-paper') }}">
          {{ $def['icon'] }}
        </div>
        <div class="flex-1">
          <div class="font-bold">{{ $def['label'] }}</div>
          <div class="text-xs text-ink/50">{{ $def['desc'] }}</div>
        </div>
        <span class="pill {{ $step->isCompleted() ? 'pill-mint' : ($step->status === 'in_progress' ? 'pill-gold' : 'pill-brand') }}">
          {{ ucfirst(str_replace('_',' ',$step->status)) }}
        </span>
        @if(!$step->isCompleted())
          @if($step->status === 'in_progress' || $step->status === 'pending')
          <button @click="complete('{{ $step->onboarding_step }}')"
                  :disabled="loading === '{{ $step->onboarding_step }}'"
                  class="btn-primary text-sm py-1.5 px-4 flex-shrink-0">
            <span x-text="loading === '{{ $step->onboarding_step }}' ? 'Saving…' : 'Mark Done'"></span>
          </button>
          @endif
        @else
          <svg class="w-5 h-5 text-mint flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        @endif
      </div>
      @if($step->completed_at)
      <div class="text-xs text-ink/30 mt-2 ml-16">Completed {{ $step->completed_at->diffForHumans() }}</div>
      @endif
    </div>
    @endforeach

    {{-- Status message --}}
    <div x-show="message" x-text="message"
         class="text-sm font-medium px-4 py-3 rounded-xl bg-mint/10 text-mint" style="display:none"></div>
  </div>
</div>

<script>
function onboarding(uuid) {
  return {
    loading: null,
    message: null,
    async complete(step) {
      this.loading = step;
      this.message = null;
      try {
        const res = await fetch(`/api/clients/${uuid}/onboarding/complete`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
          body: JSON.stringify({ step }),
        });
        const json = await res.json();
        if (res.ok) {
          this.message = json.message + ' Progress: ' + json.progress + '%';
          setTimeout(() => location.reload(), 800);
        }
      } finally {
        this.loading = null;
      }
    }
  };
}
</script>
@endsection
