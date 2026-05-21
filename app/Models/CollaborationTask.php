<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CollaborationTask extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $table = 'collaboration_tasks';

    protected $fillable = [
        'uuid', 'tenant_id', 'assigned_by', 'assigned_to', 'post_id',
        'title', 'description', 'priority', 'status',
        'due_date', 'completed_at', 'attachments', 'labels', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'attachments'  => 'array',
            'labels'       => 'array',
            'due_date'     => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($m) => $m->uuid ??= Str::uuid()->toString());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ── Relationships ─────────────────────────────────────────────

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(InternalComment::class, 'task_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function statusEnum(): TaskStatus
    {
        return TaskStatus::from($this->status);
    }

    public function priorityEnum(): TaskPriority
    {
        return TaskPriority::from($this->priority);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->statusEnum()->isTerminal();
    }

    public function markCompleted(): void
    {
        $this->update(['status' => TaskStatus::Completed->value, 'completed_at' => now()]);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where(fn ($q) =>
            $q->where('assigned_to', $userId)->orWhere('assigned_by', $userId)
        );
    }

    public function scopePending($query)
    {
        return $query->where('status', TaskStatus::Pending->value);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Rejected->value]);
    }
}
