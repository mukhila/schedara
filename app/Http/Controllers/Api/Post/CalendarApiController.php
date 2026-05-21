<?php

namespace App\Http\Controllers\API\Post;

use App\Http\Controllers\Controller;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarApiController extends Controller
{
    public function __construct(private readonly PostService $postService) {}

    public function events(Request $request): JsonResponse
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date'],
        ]);

        $tenant = app('current.tenant');
        $events = $this->postService->getCalendarEvents(
            $tenant->id,
            $request->start,
            $request->end,
        );

        return response()->json($events);
    }
}
