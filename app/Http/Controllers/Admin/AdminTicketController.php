<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Admin\AdminTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTicketController extends Controller
{
    public function __construct(private AdminTicketService $tickets) {}

    public function index(Request $request): View
    {
        $tickets = $this->tickets->paginate($request->only(['search', 'status', 'priority', 'assigned_to']));
        $stats   = $this->tickets->getStats();
        $admins  = User::where('is_super_admin', true)->get(['id', 'name']);

        return view('admin.tickets.index', compact('tickets', 'stats', 'admins'));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['user', 'tenant', 'assignee', 'messages.sender']);
        $admins = User::where('is_super_admin', true)->get(['id', 'name']);

        return view('admin.tickets.show', compact('ticket', 'admins'));
    }

    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate(['admin_id' => 'required|exists:users,id']);

        $this->tickets->assign($ticket, $request->admin_id);

        return back()->with('success', 'Ticket assigned.');
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'message'     => 'required|string|max:10000',
            'is_internal' => 'boolean',
        ]);

        $this->tickets->reply($ticket, auth()->user(), $request->message, $request->boolean('is_internal'));

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate(['status' => 'required|in:open,in_progress,waiting,resolved,closed']);

        $this->tickets->updateStatus($ticket, $request->status);

        return back()->with('success', 'Ticket status updated.');
    }
}
