<?php

namespace App\Http\Controllers\Api\Social;

use App\Http\Controllers\Controller;
use App\Models\InboxMessage;
use App\Services\Social\InboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboxApiController extends Controller
{
    public function __construct(private InboxService $inbox) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');

        $filters = $request->only(['status', 'platform', 'type', 'assigned_to', 'per_page']);

        $messages = $this->inbox->list($tenant->id, $filters);

        return response()->json([
            'data'        => $messages->items(),
            'meta'        => [
                'total'        => $messages->total(),
                'per_page'     => $messages->perPage(),
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
            ],
            'unread_count' => $this->inbox->unreadCount($tenant->id),
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $tenant  = app('current.tenant');
        $message = InboxMessage::forTenant($tenant->id)->findOrFail($id);

        $this->inbox->markRead($message);

        return response()->json(['success' => true]);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['text' => 'required|string|max:2000']);

        $tenant  = app('current.tenant');
        $message = InboxMessage::forTenant($tenant->id)->findOrFail($id);

        $ok = $this->inbox->reply($message, $validated['text']);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Reply sent.' : 'Reply failed — platform may not support replies.',
        ], $ok ? 200 : 422);
    }

    public function archive(Request $request, int $id): JsonResponse
    {
        $tenant  = app('current.tenant');
        $message = InboxMessage::forTenant($tenant->id)->findOrFail($id);

        $this->inbox->archive($message);

        return response()->json(['success' => true]);
    }

    public function assign(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['user_id' => 'required|integer|exists:users,id']);

        $tenant  = app('current.tenant');
        $message = InboxMessage::forTenant($tenant->id)->findOrFail($id);

        $this->inbox->assign($message, $validated['user_id']);

        return response()->json(['success' => true]);
    }

    public function sync(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $count  = $this->inbox->syncFromPlatforms($tenant->id);

        return response()->json(['synced' => $count]);
    }
}
