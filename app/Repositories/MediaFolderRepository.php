<?php

namespace App\Repositories;

use App\Models\MediaFolder;

class MediaFolderRepository
{
    public function tree(int $tenantId): array
    {
        $folders = MediaFolder::forTenant($tenantId)
            ->with('children')
            ->roots()
            ->orderBy('name')
            ->get();

        return $this->buildTree($folders);
    }

    public function byUuid(string $uuid, int $tenantId): ?MediaFolder
    {
        return MediaFolder::where('uuid', $uuid)
            ->where('tenant_id', $tenantId)
            ->with(['parent', 'children'])
            ->first();
    }

    private function buildTree($folders): array
    {
        return $folders->map(fn ($f) => [
            'uuid'     => $f->uuid,
            'name'     => $f->name,
            'path'     => $f->path,
            'color'    => $f->color,
            'children' => $this->buildTree($f->children),
        ])->toArray();
    }
}
