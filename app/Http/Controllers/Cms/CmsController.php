<?php

namespace App\Http\Controllers\Cms;

use App\DTOs\Media\UploadMediaDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Repositories\MediaFileRepository;
use App\Repositories\MediaFolderRepository;
use App\Services\Media\ContentApprovalService;
use App\Services\Media\MediaFolderService;
use App\Services\Media\MediaTagService;
use App\Services\Media\MediaUploadService;
use App\Services\Media\MediaVersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function __construct(
        private readonly MediaUploadService     $uploadService,
        private readonly MediaFileRepository    $repository,
        private readonly MediaFolderRepository  $folderRepo,
        private readonly MediaFolderService     $folderService,
        private readonly ContentApprovalService $approvalService,
        private readonly MediaTagService        $tagService,
    ) {}

    public function index(Request $request): View
    {
        $tenant  = app('current.tenant');
        $filters = $request->only(['search', 'type', 'folder_id', 'tag', 'approval_status', 'favorites', 'sort']);
        $media   = $this->repository->paginate($tenant->id, $filters, 24);
        $folders = $this->folderRepo->tree($tenant->id);
        $stats   = $this->repository->stats($tenant->id);
        $tags    = $this->tagService->all($tenant->id);

        $currentFolder = null;
        if (!empty($filters['folder_id'])) {
            $currentFolder = MediaFolder::where('uuid', $filters['folder_id'])->where('tenant_id', $tenant->id)->first();
        }

        return view('backend.cms.index', compact('media', 'folders', 'stats', 'tags', 'currentFolder', 'filters'));
    }

    public function show(string $uuid): View
    {
        $tenant = app('current.tenant');
        $media  = $this->repository->findByUuid($uuid, $tenant->id);
        abort_if(!$media, 404);

        return view('backend.cms.show', compact('media'));
    }

    public function upload(UploadMediaRequest $request): RedirectResponse
    {
        $tenant   = app('current.tenant');
        $folderId = null;

        if ($request->folder_id) {
            $folder   = MediaFolder::where('uuid', $request->folder_id)->where('tenant_id', $tenant->id)->first();
            $folderId = $folder?->id;
        }

        foreach ($request->file('files') as $file) {
            $dto = new UploadMediaDTO(
                file:            $file,
                tenantId:        $tenant->id,
                userId:          auth()->id(),
                folderId:        $folderId,
                altText:         $request->alt_text,
                tags:            $request->input('tags', []),
                requestApproval: (bool) $request->input('request_approval', false),
            );
            $this->uploadService->upload($dto);
        }

        return redirect()->route('cms.index')->with('success', 'Files uploaded successfully.');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $tenant = app('current.tenant');
        $media  = MediaLibrary::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
        $this->uploadService->delete($media);

        return redirect()->route('cms.index')->with('success', 'File deleted.');
    }

    public function approvals(): View
    {
        $tenant   = app('current.tenant');
        $pending  = $this->approvalService->pendingForTenant($tenant->id);

        return view('backend.cms.approvals', compact('pending'));
    }

    public function toggleFavorite(string $uuid): RedirectResponse
    {
        $tenant = app('current.tenant');
        $media  = MediaLibrary::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
        $this->uploadService->toggleFavorite($media);

        return back();
    }

    public function sharePublic(string $token): View|\Illuminate\Http\Response
    {
        $media = MediaLibrary::where('share_token', $token)->firstOrFail();

        if ($media->share_expires_at && $media->share_expires_at->isPast()) {
            abort(410, 'This share link has expired.');
        }

        return view('backend.cms.share', compact('media'));
    }
}
