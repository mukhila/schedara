{{-- Analytics sub-navigation --}}
@php
  $links = [
    ['route' => 'analytics.index',        'label' => 'Overview'],
    ['route' => 'analytics.engagement',   'label' => 'Engagement'],
    ['route' => 'analytics.reach',        'label' => 'Reach'],
    ['route' => 'analytics.followers',    'label' => 'Followers'],
    ['route' => 'analytics.campaigns',    'label' => 'Campaigns'],
    ['route' => 'analytics.demographics', 'label' => 'Demographics'],
    ['route' => 'analytics.roi',          'label' => 'ROI'],
    ['route' => 'analytics.reports',      'label' => 'Reports'],
  ];
  $current = request()->route()->getName();
@endphp
<div class="flex items-center gap-1 flex-wrap border-b border-line mb-6 -mt-2 pb-0">
  @foreach($links as $link)
  <a href="{{ route($link['route']) }}"
    class="px-3 py-2 text-sm font-semibold border-b-2 -mb-px transition-colors
      {{ $current === $link['route']
        ? 'text-brand-700 border-brand-600'
        : 'text-ink/50 border-transparent hover:text-ink hover:border-line' }}">
    {{ $link['label'] }}
  </a>
  @endforeach
</div>
