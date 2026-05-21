<?php

namespace App\Services\Admin;

use App\Events\Admin\TicketCreated;
use App\Jobs\Admin\SendTicketNotificationJob;
use App\Models\AdminActivityLog;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminTicketService
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = SupportTicket::with(['user', 'tenant', 'assignee'])
            ->withCount('messages');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('subject', 'like', "%{$filters['search']}%")
                  ->orWhere('ticket_number', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function assign(SupportTicket $ticket, int $adminId): void
    {
        $ticket->update(['assigned_to' => $adminId, 'status' => 'in_progress']);

        AdminActivityLog::record('assign', 'tickets', "Assigned ticket {$ticket->ticket_number} to admin #{$adminId}", $ticket);

        SendTicketNotificationJob::dispatch($ticket->id, 'assigned')->onQueue('default');
    }

    public function reply(SupportTicket $ticket, User $sender, string $message, bool $isInternal = false): SupportTicketMessage
    {
        $msg = $ticket->messages()->create([
            'sender_id'       => $sender->id,
            'sender_type'     => 'admin',
            'message'         => $message,
            'is_internal_note' => $isInternal,
        ]);

        if (! $isInternal) {
            if (is_null($ticket->first_response_at)) {
                $ticket->update(['first_response_at' => now(), 'status' => 'waiting']);
            }

            SendTicketNotificationJob::dispatch($ticket->id, 'replied')->onQueue('default');
        }

        AdminActivityLog::record('reply', 'tickets', "Replied to ticket {$ticket->ticket_number}", $ticket);

        return $msg;
    }

    public function updateStatus(SupportTicket $ticket, string $status): void
    {
        $data = ['status' => $status];

        if (in_array($status, ['resolved', 'closed']) && ! $ticket->resolved_at) {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        AdminActivityLog::record('status_change', 'tickets', "Changed ticket {$ticket->ticket_number} status to {$status}", $ticket);

        SendTicketNotificationJob::dispatch($ticket->id, $status)->onQueue('default');
    }

    public function getStats(): array
    {
        return SupportTicket::selectRaw("
            COUNT(*) as total,
            SUM(status IN ('open', 'in_progress', 'waiting')) as open_count,
            SUM(status IN ('resolved', 'closed')) as resolved_count,
            SUM(priority = 'critical') as critical_count,
            AVG(CASE WHEN first_response_at IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, created_at, first_response_at) END) as avg_response_minutes
        ")->first()->toArray();
    }
}
