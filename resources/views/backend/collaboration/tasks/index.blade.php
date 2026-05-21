@extends('layouts.backend')
@section('page_title', 'Task Board')

@section('styles')
<style>
.board-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem}
.board-title{font-size:1.25rem;font-weight:900;color:#021b2e}
.filters-bar{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap}
.filter-select{padding:.35rem .65rem;border:1px solid rgba(2,27,46,.15);border-radius:7px;font-family:inherit;font-size:.8rem;color:#021b2e;background:#fff;cursor:pointer}
.btn-primary{background:#65a1d8;color:#fff;font-weight:700;padding:.55rem 1.1rem;border-radius:9px;border:none;cursor:pointer;font-family:inherit;font-size:.875rem}
.btn-primary:hover{background:#4a8ccc}
.kanban-board{display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;align-items:start}
.kanban-col{background:rgba(2,27,46,.03);border-radius:12px;padding:.75rem;min-height:500px}
.col-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem}
.col-title{font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.col-count{background:rgba(2,27,46,.08);color:rgba(2,27,46,.5);font-size:.72rem;font-weight:700;padding:.1rem .4rem;border-radius:4px}
.task-card{background:#fff;border-radius:10px;padding:.875rem;margin-bottom:.6rem;border:1px solid rgba(2,27,46,.08);cursor:grab;transition:box-shadow .15s,transform .15s;position:relative}
.task-card:hover{box-shadow:0 4px 16px rgba(2,27,46,.1)}
.task-card.dragging{opacity:.5;transform:rotate(2deg)}
.task-card.drag-over{outline:2px dashed #65a1d8}
.task-prio{position:absolute;top:0;left:0;width:4px;height:100%;border-radius:4px 0 0 4px}
.task-card-title{font-size:.85rem;font-weight:700;color:#021b2e;margin-bottom:.5rem;padding-left:.5rem}
.task-card-meta{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;padding-left:.5rem}
.prio-badge{font-size:.68rem;font-weight:700;padding:.1rem .35rem;border-radius:4px}
.assignee-avatar{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:#fff}
.due-label{font-size:.72rem;color:rgba(2,27,46,.4)}
.due-overdue{color:#dc2626;font-weight:600}
.col-drop-zone{min-height:60px}
.add-task-btn{width:100%;background:transparent;border:1px dashed rgba(2,27,46,.2);border-radius:8px;padding:.5rem;font-size:.8rem;color:rgba(2,27,46,.4);cursor:pointer;font-family:inherit;margin-top:.5rem}
.add-task-btn:hover{border-color:#65a1d8;color:#65a1d8;background:rgba(101,161,216,.04)}
/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(2,27,46,.4);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:.2s}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal-box{background:#fff;border-radius:16px;padding:1.75rem;width:100%;max-width:480px;max-height:90vh;overflow-y:auto}
.modal-title{font-size:1rem;font-weight:800;color:#021b2e;margin-bottom:1.25rem}
.form-group{margin-bottom:1rem}
.form-label{font-size:.75rem;font-weight:700;color:rgba(2,27,46,.55);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.35rem;display:block}
.form-control{width:100%;padding:.6rem .875rem;border:1px solid rgba(2,27,46,.15);border-radius:9px;font-family:inherit;font-size:.875rem;color:#021b2e;outline:none;box-sizing:border-box}
.form-control:focus{border-color:#65a1d8;box-shadow:0 0 0 3px rgba(101,161,216,.15)}
.modal-actions{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem}
.btn-cancel{background:transparent;color:rgba(2,27,46,.5);font-weight:600;padding:.55rem 1rem;border-radius:8px;border:1px solid rgba(2,27,46,.15);cursor:pointer;font-family:inherit;font-size:.875rem}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}
.flash-success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.25);color:#15803d}
@media(max-width:1100px){.kanban-board{grid-template-columns:repeat(3,1fr)}}
@media(max-width:700px){.kanban-board{grid-template-columns:1fr 1fr}}
</style>
@endsection

@section('content')

@if(session('success'))
  <div class="flash flash-success">{{ session('success') }}</div>
@endif

<div class="board-header">
  <div class="board-title">Task Board</div>
  <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:.5rem;align-items:center">
      <select name="assigned_to" class="filter-select" onchange="this.form.submit()">
        <option value="">All members</option>
        @foreach($members as $m)
          <option value="{{ $m->user_id }}" {{ ($filters['assigned_to'] ?? '') == $m->user_id ? 'selected' : '' }}>
            {{ $m->user->name }}
          </option>
        @endforeach
      </select>
      <select name="priority" class="filter-select" onchange="this.form.submit()">
        <option value="">All priorities</option>
        @foreach(\App\Enums\TaskPriority::cases() as $p)
          <option value="{{ $p->value }}" {{ ($filters['priority'] ?? '') === $p->value ? 'selected' : '' }}>
            {{ $p->label() }}
          </option>
        @endforeach
      </select>
    </form>
    <button type="button" class="btn-primary" onclick="openCreateModal()">+ New Task</button>
  </div>
</div>

{{-- Kanban Board --}}
<div class="kanban-board" id="kanbanBoard">
  @foreach(\App\Enums\TaskStatus::kanbanOrder() as $status)
    <div class="kanban-col"
         data-status="{{ $status->value }}"
         ondragover="event.preventDefault(); this.classList.add('drag-over')"
         ondragleave="this.classList.remove('drag-over')"
         ondrop="onDrop(event, this)">
      <div class="col-header">
        <span class="col-title" style="color:{{ $status->color() }}">{{ $status->label() }}</span>
        <span class="col-count">{{ $board[$status->value]->count() }}</span>
      </div>
      <div class="col-drop-zone">
        @foreach($board[$status->value] as $task)
          @php
            $prio    = $task->priorityEnum();
            $overdue = $task->isOverdue();
            $avatarBg = ['#65a1d8','#8b5cf6','#10b981','#f59e0b','#ef4444'][$task->assigned_to % 5] ?? '#6b7280';
            $initial = strtoupper(mb_substr($task->assignedTo?->name ?? '?', 0, 1));
          @endphp
          <div class="task-card"
               draggable="true"
               data-uuid="{{ $task->uuid }}"
               data-status="{{ $task->status }}"
               ondragstart="onDragStart(event, this)"
               ondragend="this.classList.remove('dragging')">
            <div class="task-prio" style="background:{{ $prio->color() }}"></div>
            <a href="{{ route('collaboration.tasks.show', $task->uuid) }}" style="text-decoration:none">
              <div class="task-card-title">{{ $task->title }}</div>
            </a>
            <div class="task-card-meta">
              <span class="prio-badge" style="background:{{ $prio->color() }}18;color:{{ $prio->color() }}">{{ $prio->label() }}</span>
              @if($task->assignedTo)
                <div class="assignee-avatar" style="background:{{ $avatarBg }}" title="{{ $task->assignedTo->name }}">{{ $initial }}</div>
              @endif
              @if($task->due_date)
                <span class="due-label {{ $overdue ? 'due-overdue' : '' }}">
                  {{ $overdue ? '⚠ ' : '' }}{{ $task->due_date->format('M j') }}
                </span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
      <button type="button" class="add-task-btn" onclick="openCreateModal('{{ $status->value }}')">+ Add task</button>
    </div>
  @endforeach
</div>

{{-- Create Task Modal --}}
<div class="modal-overlay" id="createModal">
  <div class="modal-box">
    <div class="modal-title">New Task</div>
    <form method="POST" action="{{ route('collaboration.tasks.store') }}">
      @csrf
      <input type="hidden" name="status" id="newTaskStatus" value="pending">
      <div class="form-group">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" style="resize:vertical"></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
        <div class="form-group">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-control">
            @foreach(\App\Enums\TaskPriority::cases() as $p)
              <option value="{{ $p->value }}" {{ $p->value === 'medium' ? 'selected' : '' }}>{{ $p->label() }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Assign to</label>
          <select name="assigned_to" class="form-control">
            <option value="">Unassigned</option>
            @foreach($members as $m)
              <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Due Date</label>
        <input type="date" name="due_date" class="form-control">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeModal('createModal')">Cancel</button>
        <button type="submit" class="btn-primary">Create Task</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('scripts')
<script>
// ── Modal ──────────────────────────────────────────────────────────
function openCreateModal(status) {
  document.getElementById('newTaskStatus').value = status || 'pending';
  document.getElementById('createModal').classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}
document.getElementById('createModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal('createModal');
});

// ── Drag & Drop ────────────────────────────────────────────────────
let draggedUuid = null;

function onDragStart(e, card) {
  draggedUuid = card.dataset.uuid;
  card.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
}

function onDrop(e, col) {
  e.preventDefault();
  col.classList.remove('drag-over');
  const newStatus = col.dataset.status;
  if (!draggedUuid) return;

  fetch('/api/collaboration/tasks/' + draggedUuid, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'X-Tenant-ID': document.querySelector('meta[name="tenant-uuid"]')?.content ?? '',
    },
    body: JSON.stringify({ status: newStatus }),
  })
  .then(r => r.json())
  .then(() => { window.location.reload(); })
  .catch(() => { window.location.reload(); });
}

// ── Real-time (Pusher/Echo) ──────────────────────────────────────
if (typeof window.Echo !== 'undefined') {
  const tenantUuid = document.querySelector('meta[name="tenant-uuid"]')?.content;
  if (tenantUuid) {
    window.Echo.private('tenant.' + tenantUuid)
      .listen('.task.assigned', () => window.location.reload())
      .listen('.task.completed', () => window.location.reload());
  }
}
</script>
@endsection
