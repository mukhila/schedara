<?php

namespace App\Services\Media;

use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use Illuminate\Support\Str;

class MediaFolderService
{
    public function create(int $tenantId, int $userId, string $name, ?int $parentId = null): MediaFolder
    {
        $slug   = Str::slug($name);
        $parent = $parentId ? MediaFolder::find($parentId) : null;
        $path   = ($parent ? $parent->path : '') . '/' . $slug;

        return MediaFolder::create([
            'tenant_id'  => $tenantId,
            'parent_id'  => $parentId,
            'created_by' => $userId,
            'name'       => $name,
            'slug'       => $slug,
            'path'       => $path,
        ]);
    }

    public function rename(MediaFolder $folder, string $name): MediaFolder
    {
        $slug    = Str::slug($name);
        $oldPath = $folder->path;
        $newPath = ($folder->parent ? $folder->parent->path : '') . '/' . $slug;

        $folder->update(['name' => $name, 'slug' => $slug, 'path' => $newPath]);

        // Update paths of all descendants
        $this->updateDescendantPaths($folder, $oldPath, $newPath);

        return $folder->fresh();
    }

    public function move(MediaFolder $folder, ?int $newParentId): MediaFolder
    {
        $oldPath  = $folder->path;
        $newParent = $newParentId ? MediaFolder::find($newParentId) : null;
        $newPath  = ($newParent ? $newParent->path : '') . '/' . $folder->slug;

        $folder->update(['parent_id' => $newParentId, 'path' => $newPath]);
        $this->updateDescendantPaths($folder, $oldPath, $newPath);

        return $folder->fresh();
    }

    public function delete(MediaFolder $folder): void
    {
        // Move all media files in this folder to root
        MediaLibrary::where('folder_id', $folder->id)->update(['folder_id' => null]);

        // Recursively delete children
        foreach ($folder->children as $child) {
            $this->delete($child);
        }

        $folder->delete();
    }

    private function updateDescendantPaths(MediaFolder $folder, string $oldPath, string $newPath): void
    {
        MediaFolder::where('path', 'like', $oldPath . '/%')
            ->where('tenant_id', $folder->tenant_id)
            ->each(function ($child) use ($oldPath, $newPath) {
                $child->update(['path' => str_replace($oldPath, $newPath, $child->path)]);
            });
    }
}
