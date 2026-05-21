<?php

namespace App\Http\Controllers\API\Post;

use App\Http\Controllers\Controller;
use App\Http\Resources\Post\HashtagResource;
use App\Services\Post\HashtagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HashtagApiController extends Controller
{
    public function __construct(private readonly HashtagService $hashtagService) {}

    public function suggestions(Request $request): JsonResponse
    {
        $tenant      = app('current.tenant');
        $suggestions = $this->hashtagService->suggestions(
            $tenant->id,
            $request->input('q', ''),
            (int) $request->input('limit', 20),
        );

        return response()->json($suggestions);
    }

    public function trending(Request $request): JsonResponse
    {
        $tenant   = app('current.tenant');
        $trending = $this->hashtagService->trending($tenant->id);

        return response()->json($trending);
    }

    public function groups(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $groups = $this->hashtagService->groups($tenant->id);

        return response()->json($groups);
    }

    public function byGroup(Request $request, string $group): JsonResponse
    {
        $tenant = app('current.tenant');
        $tags   = $this->hashtagService->byGroup($tenant->id, $group);

        return response()->json($tags);
    }
}
