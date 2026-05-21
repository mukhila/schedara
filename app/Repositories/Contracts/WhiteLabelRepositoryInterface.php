<?php

namespace App\Repositories\Contracts;

use App\Models\WhiteLabelSetting;

interface WhiteLabelRepositoryInterface
{
    public function findByWorkspace(int $workspaceId): ?WhiteLabelSetting;

    public function findByDomain(string $domain): ?WhiteLabelSetting;

    public function upsert(int $workspaceId, array $data): WhiteLabelSetting;
}
