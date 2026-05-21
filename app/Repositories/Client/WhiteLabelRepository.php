<?php

namespace App\Repositories\Client;

use App\Models\WhiteLabelSetting;
use App\Repositories\Contracts\WhiteLabelRepositoryInterface;

class WhiteLabelRepository implements WhiteLabelRepositoryInterface
{
    public function findByWorkspace(int $workspaceId): ?WhiteLabelSetting
    {
        return WhiteLabelSetting::where('client_workspace_id', $workspaceId)->first();
    }

    public function findByDomain(string $domain): ?WhiteLabelSetting
    {
        return WhiteLabelSetting::where('custom_domain', $domain)
            ->where('domain_verified', true)
            ->first();
    }

    public function upsert(int $workspaceId, array $data): WhiteLabelSetting
    {
        return WhiteLabelSetting::updateOrCreate(
            ['client_workspace_id' => $workspaceId],
            $data,
        );
    }
}
