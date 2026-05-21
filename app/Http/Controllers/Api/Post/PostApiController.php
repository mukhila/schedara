<?php

namespace App\Http\Controllers\API\Post;

use App\DTOs\Post\CreatePostDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\BulkScheduleRequest;
use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use App\Services\Post\BulkScheduleService;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostApiController extends Controller
{
    public function __construct(
        private readonly PostService        $postService,
        private readonly BulkScheduleService $bulkService,
    ) {}

    public function index(Request $request): ResourceCollection
    {
        $tenant = app('current.tenant');
        $posts  = $this->postService->list($tenant->id, $request->only([
            'status', 'platform', 'type', 'search', 'from', 'to', 'per_page',
        ]));

        return PostResource::collection($posts);
    }

    public function store(CreatePostRequest $request): PostResource
    {
        $tenant = app('current.tenant');
        $post   = $this->postService->create(
            $tenant->id,
            auth()->id(),
            CreatePostDTO::fromRequest($request),
        );

        return new PostResource($post);
    }

    public function show(string $uuid): PostResource
    {
        $post = $this->findPost($uuid);
        return new PostResource($post->load(['platformConfigs.socialAccount', 'hashtags', 'media', 'calendarEvent', 'logs']));
    }

    public function update(UpdatePostRequest $request, string $uuid): PostResource
    {
        $post = $this->findPost($uuid);
        $post = $this->postService->update($post, CreatePostDTO::fromRequest($request));

        return new PostResource($post);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $post = $this->findPost($uuid);
        $this->postService->delete($post);

        return response()->json(['message' => 'Post deleted.']);
    }

    public function duplicate(string $uuid): PostResource
    {
        $post  = $this->findPost($uuid);
        $clone = $this->postService->duplicate($post);

        return new PostResource($clone);
    }

    public function schedule(Request $request, string $uuid): PostResource
    {
        $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'timezone'     => ['nullable', 'timezone'],
        ]);

        $post = $this->findPost($uuid);
        $post = $this->postService->scheduleNow($post, $request->scheduled_at, $request->timezone ?? 'UTC');

        return new PostResource($post);
    }

    public function cancelSchedule(string $uuid): PostResource
    {
        $post = $this->findPost($uuid);
        $post = $this->postService->cancelSchedule($post);

        return new PostResource($post);
    }

    public function bulkImport(BulkScheduleRequest $request): JsonResponse
    {
        $tenant  = app('current.tenant');
        $results = $this->bulkService->importCsv($tenant->id, auth()->id(), $request->file('file'));

        return response()->json($results);
    }

    public function sampleCsv(): \Illuminate\Http\Response
    {
        return response($this->bulkService->sampleCsvContent())
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="schedara-bulk-template.csv"');
    }

    private function findPost(string $uuid): Post
    {
        $tenant = app('current.tenant');
        $post   = Post::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();

        return $post;
    }
}
