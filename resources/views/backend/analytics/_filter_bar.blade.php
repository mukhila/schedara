{{-- Shared analytics filter bar --}}
@php
  $currentRoute = request()->route()->getName();
  $from  = $filter->range->fromString();
  $to    = $filter->range->toString();
@endphp
<div class="flex items-center gap-2 flex-wrap mb-6">
  {{-- Date presets --}}
  <form method="GET" class="inline-flex items-center bg-white border border-line rounded-lg p-1 gap-0.5">
    @foreach([7 => '7d', 30 => '30d', 90 => '90d', 365 => '1y'] as $d => $label)
    <button type="submit" name="date_from" value="{{ now()->subDays($d - 1)->toDateString() }}"
      onclick="this.form.date_to.value='{{ now()->toDateString() }}'"
      class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors
        {{ $filter->range->diffInDays() == $d ? 'bg-ink text-white' : 'text-ink/50 hover:text-ink' }}">
      {{ $label }}
    </button>
    @endforeach
    <input type="hidden" name="date_to" value="{{ $to }}">
  </form>

  {{-- Custom range --}}
  <form method="GET" class="inline-flex items-center gap-1 bg-white border border-line rounded-lg px-3 py-1.5">
    <input type="date" name="date_from" value="{{ $from }}"
      class="text-xs font-medium border-none bg-transparent outline-none text-ink cursor-pointer">
    <span class="text-ink/40 text-xs">→</span>
    <input type="date" name="date_to" value="{{ $to }}"
      class="text-xs font-medium border-none bg-transparent outline-none text-ink cursor-pointer">
    <button type="submit" class="ml-1 text-xs font-bold text-brand-600 hover:text-brand-800">Apply</button>
  </form>

  <div class="ml-auto text-xs text-ink/40 font-medium">
    {{ $from }} – {{ $to }} · {{ $filter->range->diffInDays() }} days
  </div>
</div>
