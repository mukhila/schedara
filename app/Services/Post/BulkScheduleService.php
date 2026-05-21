<?php

namespace App\Services\Post;

use App\DTOs\Post\CreatePostDTO;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;

class BulkScheduleService
{
    public function __construct(
        private readonly PostService $postService,
    ) {}

    public function importCsv(int $tenantId, int $userId, UploadedFile $file): array
    {
        $csv = Reader::createFromString($file->get());
        $csv->setHeaderOffset(0);

        $results = ['created' => 0, 'failed' => 0, 'errors' => []];

        foreach ($csv->getRecords() as $i => $row) {
            try {
                $dto = $this->rowToDto($row);
                $this->postService->create($tenantId, $userId, $dto);
                $results['created']++;
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = "Row {$i}: " . $e->getMessage();
            }
        }

        return $results;
    }

    public function sampleCsvContent(): string
    {
        return implode("\n", [
            'content,caption,type,platforms,scheduled_at,timezone,status,hashtags',
            '"Your post content here","Optional caption","text","facebook,instagram","2026-06-01 09:00:00","UTC","scheduled","marketing,growth"',
            '"Another post","","image","linkedin","2026-06-02 10:00:00","America/New_York","scheduled","business"',
        ]);
    }

    private function rowToDto(array $row): CreatePostDTO
    {
        $platforms        = array_filter(array_map('trim', explode(',', $row['platforms'] ?? '')));
        $hashtags         = array_filter(array_map('trim', explode(',', $row['hashtags'] ?? '')));
        $scheduledAt      = !empty($row['scheduled_at']) ? Carbon::parse($row['scheduled_at'])->toIso8601String() : null;

        return new CreatePostDTO(
            content:           $row['content'] ?? '',
            caption:           $row['caption'] ?: null,
            type:              $row['type']     ?? 'text',
            status:            $row['status']   ?? 'draft',
            platforms:         array_values($platforms),
            platformAccounts:  [],
            scheduledAt:       $scheduledAt,
            timezone:          $row['timezone'] ?? 'UTC',
            isEvergreen:       false,
            autoRepost:        false,
            repostFrequency:   null,
            hashtags:          array_values($hashtags),
            title:             $row['title'] ?? null,
            platformOverrides: [],
        );
    }
}
