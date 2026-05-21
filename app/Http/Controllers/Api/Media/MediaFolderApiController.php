<?php

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use App\Repositories\MediaFolderRepository;
use App\Services\Media\MediaFolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaFolderApiController extends Controller
{
    public function __construct(
        private readonly MediaFolderService    $folderService,
        private readonly MediaFolderRepository $repository,
    ) {}

    public function index(): JsonResponse
    {
        $tenant = app('current.tenant');
        return response()->json($this->repository->tree($tenant->id));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'string'],
        ]);

        $tenant   = app('current.tenant');
        $parentId = null;

        if ($request->parent_id) {
            $parentId = MediaFolder::where('uuid', $request->parent_id)->where('tenant_id', $tenant->id)->value('id');
            abort_if(!$parentId, 422, 'Parent folder not found.');
        }

        $folder = $this->folderService->create($tenant->id, auth()->id(), $request->name, $parentId);

        return response()->json([
            'uuid'  => $folder->uuid,
            'name'  => $folder->name,
            'path'  => $folder->path,
            'color' => $folder->color,
        ], 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100']]);

        $tenant = app('current.tenant');
        $folder = MediaFolder::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
        $folder = $this->folderService->rename($folder, $request->name);

        return response()->json(['uuid' => $folder->uuid, 'name' => $folder->name, 'path' => $folder->path]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $tenant = app('current.tenant');
        $folder = MediaFolder::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
        $this->folderService->delete($folder);

        return response()->json(['message' => 'Folder deleted.']);
    }

    public function move(Request $request, string $uuid): JsonResponse
    {
        $request->validate(['parent_id' => ['nullable', 'string']]);

        $tenant   = app('current.tenant');
        $folder   = MediaFolder::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
        $parentId = null;

        if ($request->parent_id) {
            $parentId = MediaFolder::where('uuid', $request->parent_id)->where('tenant_id', $tenant->id)->value('id');
        }

        $folder = $this->folderService->move($folder, $parentId);

        return response()->json(['uuid' => $folder->uuid, 'path' => $folder->path]);
    }
}
