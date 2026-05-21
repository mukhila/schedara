@extends('layouts.backend')
@section('page_title', $task->title)

@section('styles')
<style>
.task-layout{display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start}
.main-card{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);padding:1.5rem;margin-bottom:1rem}
.card-title{font-size:.82rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(2,27,46,.45);margin-bottom:1rem}
.task-title-h{font-size:1.35rem;font-weight:900;color:#021b2e;margin-bottom:.75rem}
.task-desc{font-size:.9rem;line-height:1.6;color:rgba(2,27,46,.7)}
.meta-row{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}
.meta-item{display:flex;flex-direction:column;gap:.25rem}
.meta-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:rgba(2,27,46,.4)}
.meta-value{font-size:.875rem;font-weight:600;color:#021b2e}
.status-select{padding:.35rem .65rem;border:1px solid rgba(2,27,46,.15);border-radius:7px;font-family:inherit;font-size:.82rem;cursor:pointer}
.badge{font-size:.72rem;font-weight:700;padding:.2rem .5rem;border-radius:5px}
.avatar-inline{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;color:#fff;vertical-align:middle;margin-right:.4rem}
.comment-thread{margin-bottom:1rem}
.comment-item{display:flex;gap:.75rem;margin-bottom:.875rem}
.comment-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:#fff;flex-shrink:0}
.comment-bubble{background:rgba(2,27,46,.04);border-radius:0 10px 10px 10px;padding:.65rem .875rem;flex:1}
.comment-meta{font-size:.72rem;color:rgba(2,27,46,.4);margin-top:.35rem;display:flex;gap:.75rem;align-items:center}
.reply-bubble{margin-left:2.5rem;margin-top:.5rem}
.reply-bubble .comment-bubble{background:rgba(101,161,216,.06)}
.comment-form textarea{width:100%;padding:.65rem .875rem;border:1px solid rgba(2,27,46,.15);border-radius:9px;font-family:inherit;font-size:.875rem;resize:vertical;outline:none;box-sizing:border-box}
.comment-form textarea:focus{border-color:#65a1d8;box-shadow:0 0 0 3px rgba(101,161,216,.15)}
.btn-sm{padding:.4rem .875rem;border-radius:7px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;border:none}
.btn-primary{background:#65a1d8;color:#fff}
.btn-primary:hover{background:#4a8ccc}
.btn-danger{background:rgba(239,68,68,.08);color:#dc2626;border:1px solid rgba(239,68,68,.2)}
.reaction-btn{background:none;border:1px solid rgba(2,27,46,.1);border-radius:6px;padding:.15rem .45rem;cursor:pointer;font-size:.85rem;transition:.15s}
.reaction-btn:hover{background:rgba(2,27,46,.05)}
.breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:rgba(2,27,46,.45);margin-bottom:1.25rem}
.breadcrumb a{color:#65a1d8;text-decoration:none}
.breadcrumb a:hover{text-decoration:underline}
.breadcrumb-sep{color:rgba(2,27,46,.25)}
@media(max-width:900px){.task-layout{grid-template-columns:1fr}}
</style>
@endsection

@section('content')

<div class="breadcrumb">
  <a href="{{ route('collaboration.dashboard') }}">Collaboration</a>
  <span class="breadcrumb-sep">›</span>
  <a href="{{ route('collaboration.tasks.index') }}">Tasks</a>
  <span class="breadcrumb-sep">›</span>
  <span>{{ Str::limit($task->title, 40) }}</span>
</div>

<div class="task-layout">

  {{-- Left: Task detail + comments --}}
  <div>
    <div class="main-card">
      <div class="task-title-h">{{ $task->title }}</div>

      <div class="meta-row">
        <div class="meta-item">
          <span class="meta-label">Status</span>
          <form method="POST" action="{{ route('collaboration.tasks.update', $task->uuid) }}" style="display:inline">
            @csrf @method('PUT')
            <select name="status" class="status-select" onchange="this.form.submit()">
              @foreach(\App\Enums\TaskStatus::cases() as $s)
                <option value="{{ $s->value }}" {{ $task->status === $s->value ? 'selected' : '' }}
                  style="color:{{ $s->color() }}">{{ $s->label() }}</option>
              @endforeach
            </select>
          </form>
        </div>
        <div class="meta-item">
          <span class="meta-label">Priority</span>
          <span class="badge" style="background:{{ $task->priorityEnum()->color() }}18;color:{{ $task->priorityEnum()->color() }}">
            {{ $task->priorityEnum()->label() }}
          </span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Due Date</span>
          <span class="meta-value {{ $task->isOverdue() ? 'due-overdue' : '' }}">
            {{ $task->due_date?->format('M j, Y') ?? '—' }}
          </span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Assigned to</span>
          <span class="meta-value">
            @if($task->assignedTo)
              @php $bg = ['#65a1d8','#8b5cf6','#10b981','#f59e0b','#ef4444'][$task->assigned_to % 5]; @endphp
              <span class="avatar-inline" style="background:{{ $bg }}">{{ strtoupper(mb_substr($task->assignedTo->name,0,1)) }}</span>
              {{ $task->assignedTo->name }}
            @else
              Unassigned
            @endif
          </span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Created by</span>
          <span class="meta-value">{{ $task->assignedBy->name }}</span>
        </div>
      </div>

      @if($task->description)
        <div class="task-desc">{{ $task->description }}</div>
      @endif
    </div>

    {{-- Comments --}}
    <div class="main-card">
      <div class="card-title">Comments ({{ $threadedComments->count() }})</div>

      <div class="comment-thread" id="commentThread">
        @forelse($threadedComments as $comment)
          @php
            $bg = ['#65a1d8','#8b5cf6','#10b981','#f59e0b','#ef4444'][$comment->user_id % 5];
            $initials = strtoupper(mb_substr($comment->author->name, 0, 1));
          @endphp
          <div class="comment-item" id="comment-{{ $comment->id }}">
            <div class="comment-avatar" style="background:{{ $bg }}">{{ $initials }}</div>
            <div style="flex:1">
              <div class="comment-bubble">
                <strong style="font-size:.82rem;color:#021b2e">{{ $comment->author->name }}</strong>
                <p style="margin:.35rem 0 0;font-size:.875rem;color:rgba(2,27,46,.8)">{{ $comment->comment }}</p>
                @if($comment->reactions)
                  <div style="margin-top:.5rem;display:flex;gap:.3rem;flex-wrap:wrap">
                    @foreach($comment->reactions as $emoji => $users)
                      <button class="reaction-btn" onclick="toggleReaction('{{ $comment->uuid }}', '{{ $emoji }}')">
                        {{ $emoji }} {{ count($users) }}
                      </button>
                    @endforeach
                  </div>
                @endif
                <div class="comment-meta">
                  <span>{{ $comment->created_at->diffForHumans() }}</span>
                  <button class="reaction-btn" onclick="showEmojiPicker('{{ $comment->uuid }}')">😊</button>
                  <a href="#" onclick="showReplyForm('{{ $comment->id }}'); return false;" style="color:#65a1d8;font-size:.72rem;text-decoration:none">Reply</a>
                  @if($comment->user_id === auth()->id())
                    <form method="POST" action="#" style="display:inline" onsubmit="deleteComment('{{ $comment->uuid }}'); return false;">
                      @csrf @method('DELETE')
                      <button type="submit" style="background:none;border:none;color:rgba(239,68,68,.6);font-size:.72rem;cursor:pointer">Delete</button>
                    </form>
                  @endif
                </div>
              </div>

              {{-- Reply form (hidden) --}}
              <div id="reply-form-{{ $comment->id }}" style="display:none;margin-top:.5rem">
                <form onsubmit="submitReply(event, {{ $comment->id }})">
                  <textarea placeholder="Write a reply..." rows="2" class="comment-form" style="width:100%;padding:.5rem .75rem;border:1px solid rgba(2,27,46,.15);border-radius:8px;font-family:inherit;font-size:.82rem;resize:none;outline:none;box-sizing:border-box"></textarea>
                  <div style="display:flex;gap:.5rem;margin-top:.35rem;justify-content:flex-end">
                    <button type="button" onclick="document.getElementById('reply-form-{{ $comment->id }}').style.display='none'" class="btn-sm" style="background:none;border:1px solid rgba(2,27,46,.15);color:rgba(2,27,46,.5)">Cancel</button>
                    <button type="submit" class="btn-sm btn-primary">Reply</button>
                  </div>
                </form>
              </div>

              {{-- Replies --}}
              @foreach($comment->replies as $reply)
                @php $rbg = ['#65a1d8','#8b5cf6','#10b981','#f59e0b','#ef4444'][$reply->user_id % 5]; @endphp
                <div class="comment-item reply-bubble">
                  <div class="comment-avatar" style="background:{{ $rbg }};width:26px;height:26px;font-size:.7rem">
                    {{ strtoupper(mb_substr($reply->author->name,0,1)) }}
                  </div>
                  <div class="comment-bubble">
                    <strong style="font-size:.8rem;color:#021b2e">{{ $reply->author->name }}</strong>
                    <p style="margin:.25rem 0 0;font-size:.84rem;color:rgba(2,27,46,.8)">{{ $reply->comment }}</p>
                    <div class="comment-meta"><span>{{ $reply->created_at->diffForHumans() }}</span></div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @empty
          <div style="text-align:center;color:rgba(2,27,46,.35);padding:1.25rem;font-size:.875rem">No comments yet.</div>
        @endforelse
      </div>

      {{-- Add comment --}}
      <div class="comment-form" style="margin-top:1rem">
        <form onsubmit="submitComment(event)">
          <textarea id="commentInput" placeholder="Add a comment… Use @[userId] to mention someone" rows="3" style="width:100%;padding:.65rem .875rem;border:1px solid rgba(2,27,46,.15);border-radius:9px;font-family:inherit;font-size:.875rem;resize:vertical;outline:none;box-sizing:border-box" required></textarea>
          <div style="display:flex;justify-content:flex-end;margin-top:.5rem">
            <button type="submit" class="btn-sm btn-primary">Post Comment</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Right sidebar: task actions --}}
  <div>
    <div class="main-card">
      <div class="card-title">Actions</div>
      @can('post.approve')
      <form method="POST" action="{{ route('collaboration.tasks.update', $task->uuid) }}" style="margin-bottom:.6rem">
        @csrf @method('PUT')
        <input type="hidden" name="status" value="completed">
        <button type="submit" class="btn-sm btn-primary" style="width:100%">Mark Completed</button>
      </form>
      @endcan
      <a href="{{ route('collaboration.tasks.index') }}" style="display:block;text-align:center;font-size:.82rem;color:rgba(2,27,46,.45);text-decoration:none;margin-top:.5rem">← Back to board</a>
    </div>

    <div class="main-card">
      <div class="card-title">Edit Task</div>
      <form method="POST" action="{{ route('collaboration.tasks.update', $task->uuid) }}">
        @csrf @method('PUT')
        <div style="margin-bottom:.75rem">
          <label style="font-size:.72rem;font-weight:700;color:rgba(2,27,46,.5);text-transform:uppercase;display:block;margin-bottom:.3rem">Assign to</label>
          <select name="assigned_to" style="width:100%;padding:.45rem .65rem;border:1px solid rgba(2,27,46,.15);border-radius:8px;font-family:inherit;font-size:.82rem;box-sizing:border-box">
            <option value="">Unassigned</option>
            @foreach($members as $m)
              <option value="{{ $m->user_id }}" {{ $task->assigned_to == $m->user_id ? 'selected' : '' }}>
                {{ $m->user->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div style="margin-bottom:.75rem">
          <label style="font-size:.72rem;font-weight:700;color:rgba(2,27,46,.5);text-transform:uppercase;display:block;margin-bottom:.3rem">Due Date</label>
          <input type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}" style="width:100%;padding:.45rem .65rem;border:1px solid rgba(2,27,46,.15);border-radius:8px;font-family:inherit;font-size:.82rem;box-sizing:border-box">
        </div>
        <div style="margin-bottom:.75rem">
          <label style="font-size:.72rem;font-weight:700;color:rgba(2,27,46,.5);text-transform:uppercase;display:block;margin-bottom:.3rem">Priority</label>
          <select name="priority" style="width:100%;padding:.45rem .65rem;border:1px solid rgba(2,27,46,.15);border-radius:8px;font-family:inherit;font-size:.82rem;box-sizing:border-box">
            @foreach(\App\Enums\TaskPriority::cases() as $p)
              <option value="{{ $p->value }}" {{ $task->priority === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="btn-sm" style="background:rgba(2,27,46,.06);color:#021b2e;border:1px solid rgba(2,27,46,.12);width:100%">Save Changes</button>
      </form>
    </div>

    <div class="main-card">
      <div class="card-title">Info</div>
      <div style="font-size:.8rem;color:rgba(2,27,46,.5);line-height:1.8">
        <div>Created {{ $task->created_at->format('M j, Y') }}</div>
        @if($task->completed_at)<div>Completed {{ $task->completed_at->format('M j, Y') }}</div>@endif
      </div>
    </div>
  </div>

</div>
@endsection

@section('scripts')
<script>
const taskUuid = '{{ $task->uuid }}';
const tenantId = document.querySelector('meta[name="tenant-uuid"]')?.content ?? '';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function apiHeaders() {
  return {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken,
    'X-Tenant-ID': tenantId,
  };
}

function submitComment(e) {
  e.preventDefault();
  const text = document.getElementById('commentInput').value.trim();
  if (!text) return;
  fetch('/api/collaboration/tasks/' + taskUuid + '/comments', {
    method: 'POST',
    headers: apiHeaders(),
    body: JSON.stringify({ comment: text }),
  }).then(() => window.location.reload());
}

function submitReply(e, parentId) {
  e.preventDefault();
  const textarea = e.target.querySelector('textarea');
  const text = textarea.value.trim();
  if (!text) return;
  fetch('/api/collaboration/tasks/' + taskUuid + '/comments', {
    method: 'POST',
    headers: apiHeaders(),
    body: JSON.stringify({ comment: text, parent_id: parentId }),
  }).then(() => window.location.reload());
}

function showReplyForm(commentId) {
  document.getElementById('reply-form-' + commentId).style.display = 'block';
}

function toggleReaction(uuid, emoji) {
  fetch('/api/collaboration/comments/' + uuid + '/react', {
    method: 'POST',
    headers: apiHeaders(),
    body: JSON.stringify({ emoji }),
  }).then(() => window.location.reload());
}

function showEmojiPicker(uuid) {
  const emoji = prompt('Enter emoji:');
  if (emoji) toggleReaction(uuid, emoji);
}

function deleteComment(uuid) {
  if (!confirm('Delete this comment?')) return;
  fetch('/api/collaboration/comments/' + uuid, {
    method: 'DELETE',
    headers: apiHeaders(),
  }).then(() => window.location.reload());
}
</script>
@endsection
