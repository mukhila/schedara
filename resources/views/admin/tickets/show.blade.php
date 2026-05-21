@extends('admin.layouts.admin')

@section('title', $ticket->ticket_number)
@section('heading', $ticket->ticket_number . ': ' . $ticket->subject)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Messages --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Thread --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-5 max-h-[60vh] overflow-y-auto">
            @forelse($ticket->messages as $msg)
            <div class="flex gap-3 {{ $msg->sender_type === 'admin' ? 'flex-row-reverse' : '' }}">
                <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-semibold
                    {{ $msg->is_internal_note ? 'bg-amber-100 text-amber-700' : ($msg->sender_type === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-gray-100 text-gray-700') }}">
                    {{ strtoupper(substr($msg->sender?->name ?? 'S', 0, 2)) }}
                </div>
                <div class="flex-1 max-w-lg {{ $msg->sender_type === 'admin' ? 'items-end' : '' }} flex flex-col">
                    <div class="flex items-center gap-2 mb-1 {{ $msg->sender_type === 'admin' ? 'flex-row-reverse' : '' }}">
                        <span class="text-xs font-medium text-gray-700">{{ $msg->sender?->name ?? 'System' }}</span>
                        @if($msg->is_internal_note)
                            <span class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">Internal</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="rounded-xl px-4 py-3 text-sm
                        {{ $msg->is_internal_note ? 'bg-amber-50 border border-amber-200 text-amber-900' : ($msg->sender_type === 'admin' ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-900') }}">
                        {!! nl2br(e($msg->message)) !!}
                    </div>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 py-4 text-sm">No messages yet.</p>
            @endforelse
        </div>

        {{-- Reply Box --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="space-y-3">
                @csrf
                <textarea name="message" rows="4" placeholder="Type your reply…" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-amber-500">
                        Internal note (not visible to user)
                    </label>
                    <button type="submit" class="bg-violet-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        {{-- Ticket Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Ticket Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}">
                            @csrf
                            <select name="status" onchange="this.form.submit()"
                                    class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none">
                                @foreach(['open','in_progress','waiting','resolved','closed'] as $s)
                                    <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_',' ',$s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Priority</dt>
                    <dd class="font-medium text-gray-900">{{ ucfirst($ticket->priority) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">User</dt>
                    <dd class="text-gray-700">{{ $ticket->user?->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Workspace</dt>
                    <dd class="text-gray-700">{{ $ticket->tenant?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Created</dt>
                    <dd class="text-gray-700">{{ $ticket->created_at->format('M d, Y H:i') }}</dd>
                </div>
                @if($ticket->first_response_at)
                <div class="flex justify-between">
                    <dt class="text-gray-500">First Response</dt>
                    <dd class="text-gray-700">{{ $ticket->first_response_at->format('M d, Y H:i') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Assign --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Assignment</h3>
            <form method="POST" action="{{ route('admin.tickets.assign', $ticket) }}" class="space-y-3">
                @csrf
                <select name="admin_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option value="">— Unassigned —</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-violet-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
                    Assign
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
