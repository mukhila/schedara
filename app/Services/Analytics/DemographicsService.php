<?php

namespace App\Services\Analytics;

use App\DTOs\Analytics\AnalyticsFilterDTO;
use App\Models\AnalyticsDemographic;
use Illuminate\Support\Facades\Cache;

class DemographicsService
{
    public function summary(AnalyticsFilterDTO $f): array
    {
        $cacheKey = "analytics:demographics:{$f->tenantId}:{$f->range->fromString()}:{$f->range->toString()}";

        return Cache::remember($cacheKey, 600, function () use ($f) {
            $dimensions = ['age', 'gender', 'country', 'city', 'device'];
            $result     = [];

            foreach ($dimensions as $dim) {
                $result[$dim] = AnalyticsDemographic::forTenant($f->tenantId)
                    ->inRange($f->range->fromString(), $f->range->toString())
                    ->dimension($dim)
                    ->when($f->platforms, fn ($q) => $q->whereIn('platform', $f->platforms))
                    ->selectRaw('dimension_value, SUM(count) AS count, AVG(percentage) AS percentage')
                    ->groupBy('dimension_value')
                    ->orderByDesc('count')
                    ->get()
                    ->toArray();
            }

            return $result;
        });
    }

    public function upsert(int $tenantId, int $accountId, string $platform, string $date, array $rows): void
    {
        foreach ($rows as $row) {
            AnalyticsDemographic::updateOrCreate(
                [
                    'social_account_id' => $accountId,
                    'date'              => $date,
                    'dimension'         => $row['dimension'],
                    'dimension_value'   => $row['value'],
                ],
                [
                    'tenant_id'  => $tenantId,
                    'platform'   => $platform,
                    'count'      => $row['count'],
                    'percentage' => $row['percentage'] ?? 0,
                ]
            );
        }
    }
}
