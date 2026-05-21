<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\Admin\AdminTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTicketApiController extends Controller
{
    public function __construct(private AdminTicketService $tickets) {}

    public function index(Request $request): JsonResponse
    {
        $tickets = $this->tickets->paginate($request->only(['search', 'status', 'priority']), 20);

        return response()->json($tickets);
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->tickets->getStats());
    }

    public function reply(Request $request, SupportTicket $ticket): JsonResponse
    {
        $request->validate([
            'message'     => 'required|string|max:10000',
            'is_internal' => 'boolean',
        ]);

        $msg = $this->tickets->reply($ticket, auth()->user(), $request->message, $request->boolean('is_internal'));

        return response()->json($msg->load('sender'), 201);
    }

    public function updateStatus(Request $request, SupportTicket $ticket): JsonResponse
    {
        $request->validate(['status' => 'required|in:open,in_progress,waiting,resolved,closed']);

        $this->tickets->updateStatus($ticket, $request->status);

        return response()->json(['message' => 'Status updated.']);
    }
}
