<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\AnalyticsClickTracking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ClickTrackingService
{
    public function createLink(int $tenantId, array $data): AnalyticsClickTracking
    {
        $shortCode = Str::random(8);
        $shortUrl  = $this->shortenWithBitly($data['url'], $shortCode);

        return AnalyticsClickTracking::create([
            'tenant_id'    => $tenantId,
            'post_id'      => $data['post_id'] ?? null,
            'campaign_id'  => $data['campaign_id'] ?? null,
            'platform'     => $data['platform'] ?? null,
            'url'          => $data['url'],
            'short_code'   => $shortCode,
            'utm_source'   => $data['utm_source'] ?? null,
            'utm_medium'   => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_content'  => $data['utm_content'] ?? null,
        ]);
    }

    public function recordClick(string $shortCode, array $meta = []): ?AnalyticsClickTracking
    {
        $link = AnalyticsClickTracking::where('short_code', $shortCode)->first();

        if (!$link) {
            return null;
        }

        $link->increment('clicks');
        $link->update([
            'device'     => $meta['device'] ?? null,
            'country'    => $meta['country'] ?? null,
            'referrer'   => $meta['referrer'] ?? null,
            'clicked_at' => now(),
        ]);

        // Sync Bitly stats asynchronously
        $this->syncBitlyStats($link);

        return $link;
    }

    public function syncBitlyStats(AnalyticsClickTracking $link): void
    {
        $apiKey = config('analytics.bitly_api_key');
        if (!$apiKey || !$link->short_code) {
            return;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(5)
                ->get('https://api-ssl.bitly.com/v4/bitlinks/' . urlencode('bit.ly/' . $link->short_code) . '/clicks/summary', [
                    'unit'       => 'day',
                    'units'      => 30,
                    'rollup'     => true,
                ]);

            if ($response->successful()) {
                $link->update(['clicks' => $response->json('link_clicks', $link->clicks)]);
            }
        } catch (\Throwable) {
            // Non-fatal: Bitly sync failure doesn't affect core tracking
        }
    }

    public function summary(AnalyticsFilterDTO $f): array
    {
        $links = AnalyticsClickTracking::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->selectRaw('
                platform,
                COALESCE(SUM(clicks), 0)        AS clicks,
                COALESCE(SUM(unique_clicks), 0) AS unique_clicks,
                COALESCE(SUM(conversions), 0)   AS conversions,
                COALESCE(SUM(revenue), 0)       AS revenue
            ')
            ->groupBy('platform')
            ->get();

        $topLinks = AnalyticsClickTracking::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->orderByDesc('clicks')
            ->limit(10)
            ->get();

        $byDevice = AnalyticsClickTracking::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->selectRaw('device, SUM(clicks) AS clicks')
            ->groupBy('device')
            ->pluck('clicks', 'device');

        $byCountry = AnalyticsClickTracking::forTenant($f->tenantId)
            ->inRange($f->range->fromString(), $f->range->toString())
            ->selectRaw('country, SUM(clicks) AS clicks')
            ->groupBy('country')
            ->orderByDesc('clicks')
            ->limit(10)
            ->pluck('clicks', 'country');

        return [
            'by_platform' => $links->toArray(),
            'top_links'   => $topLinks->toArray(),
            'by_device'   => $byDevice->toArray(),
            'by_country'  => $byCountry->toArray(),
        ];
    }

    // ── Bitly helper ─────────────────────────────────────────────

    private function shortenWithBitly(string $url, string $fallbackCode): string
    {
        $apiKey = config('analytics.bitly_api_key');

        if (!$apiKey) {
            return url('/r/' . $fallbackCode);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(5)
                ->post('https://api-ssl.bitly.com/v4/shorten', [
                    'long_url' => $url,
                ]);

            if ($response->successful()) {
                return $response->json('link', url('/r/' . $fallbackCode));
            }
        } catch (\Throwable) {
            // Fall through to local short link
        }

        return url('/r/' . $fallbackCode);
    }
}
