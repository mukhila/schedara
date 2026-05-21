<?php

namespace App\DTOs\Analytics;

class AnalyticsFilterDTO
{
    public function __construct(
        public readonly int       $tenantId,
        public readonly DateRangeDTO $range,
        public readonly ?array    $platforms     = null,
        public readonly ?array    $accountIds    = null,
        public readonly ?array    $campaignIds   = null,
        public readonly ?string   $groupBy       = 'day',   // day, week, month
        public readonly ?string   $metric        = null,
        public readonly int       $limit         = 100,
    ) {}

    public static function fromRequest(array $data, int $tenantId): self
    {
        $range = isset($data['date_from'], $data['date_to'])
            ? new DateRangeDTO($data['date_from'], $data['date_to'])
            : DateRangeDTO::lastMonth();

        return new self(
            tenantId:   $tenantId,
            range:      $range,
            platforms:  $data['platforms']   ?? null,
            accountIds: $data['account_ids'] ?? null,
            campaignIds:$data['campaign_ids'] ?? null,
            groupBy:    $data['group_by']    ?? 'day',
            metric:     $data['metric']      ?? null,
            limit:      (int) ($data['limit'] ?? 100),
        );
    }
}
