<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function __construct(private NotificationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $paginator = $this->service->forUser(
            userId:   $user->id,
            perPage:  (int) $request->input('per_page', 20),
            category: $request->input('category'),
            filter:   $request->input('filter'),
        );

        return response()->json($paginator);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['count' => $this->service->unreadCount($request->user()->id)]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $this->service->markRead($request->user()->id, $id);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->service->markAllRead($request->user()->id);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->delete($request->user()->id, $id);

        return response()->json(['message' => 'Deleted.']);
    }

    public function clearAll(Request $request): JsonResponse
    {
        $this->service->clearAll($request->user()->id);

        return response()->json(['message' => 'All notifications cleared.']);
    }
}
