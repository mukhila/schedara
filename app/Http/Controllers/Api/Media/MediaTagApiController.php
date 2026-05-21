<?php

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Services\Media\MediaTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaTagApiController extends Controller
{
    public function __construct(private readonly MediaTagService $tagService) {}

    public function index(): JsonResponse
    {
        $tenant = app('current.tenant');
        return response()->json($this->tagService->all($tenant->id));
    }

    public function suggestions(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        return response()->json($this->tagService->suggestions($tenant->id, $request->input('q', '')));
    }
}
