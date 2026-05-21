<?php

namespace App\DTOs\Media;

use Illuminate\Http\UploadedFile;

readonly class UploadMediaDTO
{
    public function __construct(
        public UploadedFile $file,
        public int          $tenantId,
        public int          $userId,
        public ?int         $folderId,
        public ?string      $altText,
        public array        $tags,
        public bool         $requestApproval,
    ) {}
}
