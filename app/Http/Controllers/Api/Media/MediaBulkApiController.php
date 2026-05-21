<?php

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Services\Media\ContentApprovalService;
use App\Services\Media\MediaTagService;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MediaBulkApiController extends Controller
{
    public function __construct(
        private readonly MediaTagService        $tagService,
        private readonly ContentApprovalService $approvalService,
        private readonly MediaUploadService     $uploadService,
    ) {}

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['uuids' => ['required', 'array', 'min:1', 'max:100']]);

        $tenant = app('current.tenant');
        $files  = MediaLibrary::where('tenant_id', $tenant->id)
            ->whereIn('uuid', $request->uuids)
            ->get();

        $deleted = 0;
        foreach ($files as $file) {
            $this->uploadService->delete($file);
            $deleted++;
        }

        return response()->json(['message' => "{$deleted} file(s) deleted."]);
    }

    public function move(Request $request): JsonResponse
    {
        $request->validate([
            'uuids'     => ['required', 'array', 'min:1'],
            'folder_id' => ['nullable', 'string'],
        ]);

        $tenant   = app('current.tenant');
        $folderId = null;

        if ($request->folder_id) {
            $folderId = MediaFolder::where('uuid', $request->folder_id)
                ->where('tenant_id', $tenant->id)
                ->value('id');
        }

        $count = MediaLibrary::where('tenant_id', $tenant->id)
            ->whereIn('uuid', $request->uuids)
            ->update(['folder_id' => $folderId]);

        return response()->json(['message' => "{$count} file(s) moved."]);
    }

    public function tag(Request $request): JsonResponse
    {
        $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'tags'  => ['required', 'array', 'min:1'],
            'tags.*'=> ['string', 'max:100'],
        ]);

        $tenant = app('current.tenant');
        $files  = MediaLibrary::where('tenant_id', $tenant->id)
            ->whereIn('uuid', $request->uuids)
            ->get();

        foreach ($files as $file) {
            $this->tagService->syncTags($file, $tenant->id, $request->tags);
        }

        return response()->json(['message' => count($files) . ' file(s) tagged.']);
    }

    public function approve(Request $request): JsonResponse
    {
        $request->validate([
            'uuids'    => ['required', 'array', 'min:1'],
            'comments' => ['nullable', 'string'],
        ]);

        $tenant    = app('current.tenant');
        $userId    = auth()->id();
        $approved  = 0;

        $files = MediaLibrary::where('tenant_id', $tenant->id)
            ->whereIn('uuid', $request->uuids)
            ->where('approval_status', 'pending')
            ->with('approvals')
            ->get();

        foreach ($files as $file) {
            $approval = $file->approvals()->where('status', 'pending')->first();
            if ($approval) {
                $this->approvalService->approve($approval, $userId, $request->comments);
                $approved++;
            }
        }

        return response()->json(['message' => "{$approved} file(s) approved."]);
    }

    public function favorite(Request $request): JsonResponse
    {
        $request->validate([
            'uuids'     => ['required', 'array', 'min:1'],
            'is_favorite'=> ['required', 'boolean'],
        ]);

        $tenant = app('current.tenant');
        $count  = MediaLibrary::where('tenant_id', $tenant->id)
            ->whereIn('uuid', $request->uuids)
            ->update(['is_favorite' => $request->is_favorite]);

        return response()->json(['message' => "{$count} file(s) updated."]);
    }

    public function duplicate(Request $request): JsonResponse
    {
        $request->validate(['uuids' => ['required', 'array', 'min:1', 'max:50']]);

        $tenant    = app('current.tenant');
        $files     = MediaLibrary::where('tenant_id', $tenant->id)
            ->whereIn('uuid', $request->uuids)
            ->get();

        $duplicates = [];
        foreach ($files as $file) {
            if ($file->file_hash) {
                $dups = MediaLibrary::where('tenant_id', $tenant->id)
                    ->where('file_hash', $file->file_hash)
                    ->where('id', '!=', $file->id)
                    ->get(['uuid', 'name', 'type', 'created_at'])
                    ->toArray();

                if (!empty($dups)) {
                    $duplicates[$file->uuid] = $dups;
                }
            }
        }

        return response()->json(['duplicates' => $duplicates]);
    }
}
