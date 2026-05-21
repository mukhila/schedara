<?php

namespace App\Http\Controllers\API\Post;

use App\Http\Controllers\Controller;
use App\Services\AI\AiCaptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiCaptionApiController extends Controller
{
    public function __construct(private readonly AiCaptionService $aiService) {}

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'content'  => ['required', 'string'],
            'platform' => ['required', 'string'],
            'tone'     => ['nullable', 'in:professional,casual,humorous,inspirational,educational'],
        ]);

        $caption = $this->aiService->generateCaption(
            $request->content,
            $request->platform,
            $request->input('tone', 'professional'),
        );

        return response()->json(['caption' => $caption]);
    }

    public function hashtags(Request $request): JsonResponse
    {
        $request->validate([
            'content'  => ['required', 'string'],
            'platform' => ['required', 'string'],
            'count'    => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $tags = $this->aiService->generateHashtags(
            $request->content,
            $request->platform,
            $request->input('count', 10),
        );

        return response()->json(['hashtags' => $tags]);
    }

    public function bestTime(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => ['required', 'string'],
            'industry' => ['nullable', 'string'],
        ]);

        $suggestion = $this->aiService->suggestBestTime(
            $request->platform,
            $request->input('industry', 'general'),
        );

        return response()->json($suggestion);
    }
}
