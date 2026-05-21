<?php

namespace App\Http\Controllers\Api\Media;

use App\DTOs\Media\UploadMediaDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Http\Resources\Media\MediaFileResource;
use App\Jobs\Media\AIContentTaggingJob;
use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Repositories\MediaFileRepository;
use App\Services\Media\ContentApprovalService;
use App\Services\Media\DuplicateDetectionService;
use App\Services\Media\MediaSearchService;
use App\Services\Media\MediaTagService;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MediaApiController extends Controller
{
    public function __construct(
        private readonly MediaUploadService       $uploadService,
        private readonly MediaFileRepository      $repository,
        private readonly ContentApprovalService   $approvalService,
        private readonly MediaTagService          $tagService,
        private readonly MediaSearchService       $searchService,
        private readonly DuplicateDetectionService $duplicateService,
    ) {}

    public function index(Request $request): ResourceCollection
    {
        $tenant = app('current.tenant');
        $media  = $this->repository->paginate($tenant->id, $request->only([
            'search', 'type', 'folder_id', 'tag', 'approval_status', 'favorites', 'sort',
        ]));

        return MediaFileResource::collection($media);
    }

    public function upload(UploadMediaRequest $request): JsonResponse
    {
        $tenant  = app('current.tenant');
        $results = [];

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

            $media     = $this->uploadService->upload($dto);
            $results[] = new MediaFileResource($media);
        }

        return response()->json(['data' => $results], 201);
    }

    public function show(string $uuid): MediaFileResource
    {
        $tenant = app('current.tenant');
        $media  = $this->repository->findByUuid($uuid, $tenant->id);

        abort_if(!$media, 404);

        return new MediaFileResource($media);
    }

    public function update(Request $request, string $uuid): MediaFileResource
    {
        $media = $this->findMedia($uuid);

        $media->update($request->only(['name', 'alt_text']));

        if ($request->has('tags')) {
            $this->tagService->syncTags($media, $media->tenant_id, $request->tags);
        }

        return new MediaFileResource($media->fresh(['mediaTags', 'folder']));
    }

    public function destroy(string $uuid): JsonResponse
    {
        $media = $this->findMedia($uuid);
        $this->uploadService->delete($media);

        return response()->json(['message' => 'Deleted.']);
    }

    public function move(Request $request): JsonResponse
    {
        $request->validate([
            'uuids'     => ['required', 'array'],
            'folder_id' => ['nullable', 'string'],
        ]);

        $tenant   = app('current.tenant');
        $folderId = null;

        if ($request->folder_id) {
            $folder   = MediaFolder::where('uuid', $request->folder_id)->where('tenant_id', $tenant->id)->first();
            $folderId = $folder?->id;
        }

        MediaLibrary::where('tenant_id', $tenant->id)
            ->whereIn('uuid', $request->uuids)
            ->update(['folder_id' => $folderId]);

        return response()->json(['message' => 'Moved.']);
    }

    public function optimize(Request $request, string $uuid): JsonResponse
    {
        $media = $this->findMedia($uuid);
        \App\Jobs\Media\OptimizeImageJob::dispatch($media);

        return response()->json(['message' => 'Image optimization queued.']);
    }

    public function compress(Request $request, string $uuid): JsonResponse
    {
        $media = $this->findMedia($uuid);
        \App\Jobs\Media\CompressVideoJob::dispatch($media);

        return response()->json(['message' => 'Video compression queued.']);
    }

    public function tag(Request $request, string $uuid): MediaFileResource
    {
        $request->validate(['tags' => ['required', 'array'], 'tags.*' => ['string']]);
        $media = $this->findMedia($uuid);
        $this->tagService->syncTags($media, $media->tenant_id, $request->tags);

        return new MediaFileResource($media->fresh('mediaTags'));
    }

    public function requestApproval(Request $request, string $uuid): JsonResponse
    {
        $media    = $this->findMedia($uuid);
        $approval = $this->approvalService->request($media, auth()->id());

        return response()->json(['message' => 'Approval requested.', 'approval_id' => $approval->id]);
    }

    public function approve(Request $request, string $uuid): JsonResponse
    {
        $media    = $this->findMedia($uuid);
        $approval = $media->approvals()->where('status', 'pending')->firstOrFail();
        $this->approvalService->approve($approval, auth()->id(), $request->input('comments'));

        return response()->json(['message' => 'Approved.']);
    }

    public function reject(Request $request, string $uuid): JsonResponse
    {
        $request->validate(['comments' => ['required', 'string']]);
        $media    = $this->findMedia($uuid);
        $approval = $media->approvals()->where('status', 'pending')->firstOrFail();
        $this->approvalService->reject($approval, auth()->id(), $request->comments);

        return response()->json(['message' => 'Rejected.']);
    }

    public function toggleFavorite(string $uuid): MediaFileResource
    {
        $media = $this->findMedia($uuid);
        $media = $this->uploadService->toggleFavorite($media);

        return new MediaFileResource($media);
    }

    public function shareLink(Request $request, string $uuid): JsonResponse
    {
        $request->validate(['expires_in_hours' => ['nullable', 'integer', 'min:1', 'max:720']]);
        $media = $this->findMedia($uuid);
        $media = $this->uploadService->generateShareLink($media, $request->input('expires_in_hours', 72));

        return response()->json(['share_url' => $media->shareUrl()]);
    }

    public function aiTag(string $uuid): JsonResponse
    {
        $media = $this->findMedia($uuid);
        AIContentTaggingJob::dispatch($media);

        return response()->json(['message' => 'AI tagging queued.']);
    }

    public function search(Request $request): ResourceCollection
    {
        $tenant  = app('current.tenant');
        $results = $this->searchService->search($tenant->id, $request->only([
            'q', 'type', 'extension', 'tag', 'folder_id', 'approval_status', 'favorites',
            'uploaded_by', 'date_from', 'date_to', 'min_size', 'max_size', 'sort',
        ]));

        return MediaFileResource::collection($results);
    }

    public function duplicates(): JsonResponse
    {
        $tenant = app('current.tenant');
        return response()->json($this->searchService->findDuplicates($tenant->id));
    }

    public function stats(): JsonResponse
    {
        $tenant = app('current.tenant');
        return response()->json($this->repository->stats($tenant->id));
    }

    private function findMedia(string $uuid): MediaLibrary
    {
        $tenant = app('current.tenant');
        return MediaLibrary::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
    }
}
