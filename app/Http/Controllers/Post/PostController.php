<?php

namespace App\Http\Controllers\Post;

use App\DTOs\Post\CreatePostDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\BulkScheduleRequest;
use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Services\Post\BulkScheduleService;
use App\Services\Post\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService        $postService,
        private readonly BulkScheduleService $bulkService,
    ) {}

    public function index(Request $request): View
    {
        $tenant = app('current.tenant');
        $posts  = $this->postService->list($tenant->id, $request->only([
            'status', 'platform', 'type', 'search', 'from', 'to', 'per_page',
        ]));

        return view('backend.posts.index', compact('posts'));
    }

    public function create(): View
    {
        $tenant   = app('current.tenant');
        $accounts = SocialAccount::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('platform')
            ->get();

        return view('backend.posts.create', compact('accounts'));
    }

    public function store(CreatePostRequest $request): RedirectResponse
    {
        $tenant = app('current.tenant');
        $post   = $this->postService->create(
            $tenant->id,
            auth()->id(),
            CreatePostDTO::fromRequest($request),
        );

        return redirect()->route('posts.show', $post->uuid)
            ->with('success', 'Post created successfully.');
    }

    public function show(string $uuid): View
    {
        $tenant = app('current.tenant');
        $post   = Post::where('uuid', $uuid)->where('tenant_id', $tenant->id)
            ->with(['platformConfigs.socialAccount', 'hashtags', 'media', 'logs'])
            ->firstOrFail();

        return view('backend.posts.show', compact('post'));
    }

    public function edit(string $uuid): View
    {
        $tenant   = app('current.tenant');
        $post     = Post::where('uuid', $uuid)->where('tenant_id', $tenant->id)
            ->with(['platformConfigs', 'hashtags', 'media'])
            ->firstOrFail();

        $accounts = SocialAccount::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('platform')
            ->get();

        return view('backend.posts.edit', compact('post', 'accounts'));
    }

    public function update(UpdatePostRequest $request, string $uuid): RedirectResponse
    {
        $tenant = app('current.tenant');
        $post   = Post::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
        $this->postService->update($post, CreatePostDTO::fromRequest($request));

        return redirect()->route('posts.show', $post->uuid)
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $tenant = app('current.tenant');
        $post   = Post::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
        $this->postService->delete($post);

        return redirect()->route('posts.index')->with('success', 'Post deleted.');
    }

    public function calendar(): View
    {
        return view('backend.posts.calendar');
    }

    public function drafts(Request $request): View
    {
        $tenant = app('current.tenant');
        $posts  = $this->postService->list($tenant->id, array_merge(
            $request->only(['search', 'per_page']),
            ['status' => 'draft'],
        ));

        return view('backend.posts.drafts', compact('posts'));
    }

    public function bulkImport(BulkScheduleRequest $request): RedirectResponse
    {
        $tenant  = app('current.tenant');
        $results = $this->bulkService->importCsv($tenant->id, auth()->id(), $request->file('file'));

        $msg = "Imported {$results['created']} posts.";
        if ($results['failed']) {
            $msg .= " {$results['failed']} failed.";
        }

        return redirect()->route('posts.index')->with('success', $msg);
    }
}
