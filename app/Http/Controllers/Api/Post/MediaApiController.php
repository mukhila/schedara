<?php

namespace App\Http\Controllers\API\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\UploadMediaRequest;
use App\Http\Resources\Post\PostMediaResource;
use App\Models\Post;
use App\Models\PostMedia;
use App\Services\Post\MediaService;
use App\Services\Post\WatermarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaApiController extends Controller
{
    public function __construct(
        private readonly MediaService     $mediaService,
        private readonly WatermarkService $watermarkService,
    ) {}

    public function upload(UploadMediaRequest $request, string $postUuid): PostMediaResource
    {
        $post  = $this->findPost($postUuid);
        $media = $this->mediaService->upload(
            $post,
            $request->file('file'),
            (int) $request->input('sort_order', 0),
        );

        return new PostMediaResource($media);
    }

    public function destroy(string $postUuid, string $mediaUuid): JsonResponse
    {
        $post  = $this->findPost($postUuid);
        $media = PostMedia::where('uuid', $mediaUuid)->where('post_id', $post->id)->firstOrFail();
        $this->mediaService->delete($media);

        return response()->json(['message' => 'Media deleted.']);
    }

    public function reorder(Request $request, string $postUuid): JsonResponse
    {
        $request->validate(['uuids' => ['required', 'array']]);
        $post = $this->findPost($postUuid);
        $this->mediaService->reorder($post, $request->uuids);

        return response()->json(['message' => 'Reordered.']);
    }

    public function applyWatermark(string $postUuid, string $mediaUuid): PostMediaResource
    {
        $post  = $this->findPost($postUuid);
        $media = PostMedia::where('uuid', $mediaUuid)->where('post_id', $post->id)->firstOrFail();
        $media = $this->watermarkService->apply($media);

        return new PostMediaResource($media);
    }

    private function findPost(string $uuid): Post
    {
        $tenant = app('current.tenant');
        return Post::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
    }
}
