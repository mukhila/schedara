<?php

namespace App\DTOs\Client;

readonly class GenerateReportDTO
{
    public function __construct(
        public string  $reportName,
        public string  $reportType,
        public string  $format        = 'pdf',
        public array   $reportConfig  = [],
        public bool    $emailDelivery = false,
        public bool    $isScheduled   = false,
        public ?string $scheduleCron  = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            reportName:    $data['report_name'],
            reportType:    $data['report_type'],
            format:        $data['format'] ?? 'pdf',
            reportConfig:  $data['report_config'] ?? [],
            emailDelivery: (bool) ($data['email_delivery'] ?? false),
            isScheduled:   (bool) ($data['is_scheduled'] ?? false),
            scheduleCron:  $data['schedule_cron'] ?? null,
        );
    }
}
