{{--
  Widget: Engagement over time
  Vars: $timeSeries, $dateRange
--}}
<div class="card p-6">
  <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
    <div>
      <h3 class="text-lg font-bold">Performance over time</h3>
      <p class="text-xs text-ink/50 mt-1">Engagement · {{ $dateRange['from'] ?? '' }} – {{ $dateRange['to'] ?? '' }}</p>
    </div>
    <div class="flex items-center gap-1 text-[11px] font-bold">
      <button onclick="wSwitchMetric('engagement')" id="wBtn-engagement"
        class="px-2.5 py-1 rounded-md bg-brand-50 text-brand-700 transition-colors">Engagement</button>
      <button onclick="wSwitchMetric('reach')" id="wBtn-reach"
        class="px-2.5 py-1 rounded-md text-ink/50 hover:text-ink transition-colors">Reach</button>
      <button onclick="wSwitchMetric('clicks')" id="wBtn-clicks"
        class="px-2.5 py-1 rounded-md text-ink/50 hover:text-ink transition-colors">Clicks</button>
    </div>
  </div>
  <canvas id="wChartTimeSeries" style="height:240px;width:100%"></canvas>
</div>
