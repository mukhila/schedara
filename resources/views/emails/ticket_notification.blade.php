<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body { font-family: -apple-system, sans-serif; margin: 0; background: #f9fafb; }
.wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; }
.header { background: #7c3aed; padding: 24px 32px; }
.header h1 { color: #fff; font-size: 18px; margin: 0; }
.body { padding: 28px 32px; }
.footer { padding: 16px 32px; background: #f3f4f6; color: #6b7280; font-size: 12px; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
.badge-open { background: #dbeafe; color: #1d4ed8; }
.badge-resolved { background: #d1fae5; color: #065f46; }
.badge-critical { background: #fee2e2; color: #991b1b; }
</style></head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Support Ticket Update</h1>
    </div>
    <div class="body">
        <p style="color:#374151;margin-top:0">Hi {{ $ticket->user?->name }},</p>
        <p style="color:#374151">Your support ticket has been updated.</p>
        <table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:14px">
            <tr>
                <td style="padding:8px 0;color:#6b7280;width:130px">Ticket #</td>
                <td style="padding:8px 0;color:#111827;font-weight:600">{{ $ticket->ticket_number }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#6b7280">Subject</td>
                <td style="padding:8px 0;color:#111827">{{ $ticket->subject }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#6b7280">Status</td>
                <td style="padding:8px 0">
                    <span class="badge badge-{{ $ticket->isResolved() ? 'resolved' : 'open' }}">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#6b7280">Priority</td>
                <td style="padding:8px 0">
                    <span class="badge {{ $ticket->priority === 'critical' ? 'badge-critical' : '' }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </td>
            </tr>
        </table>
        @if($ticket->messages->last() && !$ticket->messages->last()->is_internal_note)
        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin:16px 0">
            <p style="margin:0 0 6px;font-size:12px;color:#9ca3af">Latest message from {{ $ticket->messages->last()->sender?->name }}</p>
            <p style="margin:0;color:#374151;font-size:14px;line-height:1.6">{{ Str::limit($ticket->messages->last()->message, 300) }}</p>
        </div>
        @endif
        <p style="color:#6b7280;font-size:13px">You can reply to this email or log into your account to view the full conversation.</p>
    </div>
    <div class="footer">
        <p style="margin:0">This email was sent from Schedara Support System. Please do not reply directly to this email.</p>
    </div>
</div>
</body>
</html>
