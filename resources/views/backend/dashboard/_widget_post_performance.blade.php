{{--
  Widget: Post Performance
  Vars: $postPerf, $dateRange
--}}
<div class="card p-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-lg font-bold">Top performing posts</h3>
      <p class="text-xs text-ink/50 mt-1">Sorted by engagement · last {{ $dateRange['days'] ?? 30 }} days</p>
    </div>
    <a href="{{ route('analytics.engagement') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 transition-colors">View all →</a>
  </div>

  @if(!empty($postPerf) && count($postPerf) > 0)
  <div class="overflow-x-auto -mx-2">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-[10px] font-bold uppercase tracking-wider text-ink/40 text-left">
          <th class="px-2 py-2">Post</th>
          <th class="px-2 py-2 text-right">Platform</th>
          <th class="px-2 py-2 text-right">Reach</th>
          <th class="px-2 py-2 text-right">Engagement</th>
          <th class="px-2 py-2 text-right">Eng. Rate</th>
          <th class="px-2 py-2 text-right">Clicks</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        @foreach(array_slice((array) $postPerf, 0, 8) as $post)
        @php
          $engRate = ($post['engagement_rate'] ?? 0);
        @endphp
        <tr class="hover:bg-paper/80 transition-colors">
          <td class="px-2 py-3 max-w-[200px]">
            <div class="font-semibold text-ink truncate">{{ Str::limit($post['caption'] ?? 'Untitled post', 50) }}</div>
            <div class="text-xs text-ink/40 mt-0.5">{{ \Carbon\Carbon::parse($post['published_at'] ?? now())->diffForHumans() }}</div>
          </td>
          <td class="px-2 py-3 text-right">
            <span class="pill text-xs capitalize">{{ $post['platform'] ?? '—' }}</span>
          </td>
          <td class="px-2 py-3 text-right font-semibold">{{ number_format($post['reach'] ?? 0) }}</td>
          <td class="px-2 py-3 text-right font-semibold">{{ number_format($post['engagement_count'] ?? 0) }}</td>
          <td class="px-2 py-3 text-right">
            <span class="pill text-xs {{ $engRate >= 5 ? 'pill-mint' : ($engRate >= 2 ? 'pill-gold' : 'pill-coral') }}">
              {{ number_format($engRate, 2) }}%
            </span>
          </td>
          <td class="px-2 py-3 text-right font-semibold">{{ number_format($post['clicks'] ?? 0) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="flex flex-col items-center justify-center h-32 text-ink/40 gap-2">
    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M3 3h18v18H3z"/><path d="M9 9h6M9 13h4"/>
    </svg>
    <p class="text-sm">No post data for this period</p>
  </div>
  @endif
</div>
