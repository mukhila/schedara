<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InboxMessage extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'social_account_id',
        'platform',
        'external_id',
        'type',
        'from_user',
        'content',
        'sentiment',
        'status',
        'assigned_to',
        'tags',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'from_user'   => 'array',
            'tags'        => 'array',
            'received_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function markRead(): void
    {
        if ($this->status === 'unread') {
            $this->update(['status' => 'read']);
        }
    }

    public function assignTo(int $userId): void
    {
        $this->update(['assigned_to' => $userId, 'status' => 'read']);
    }

    public function senderName(): string
    {
        return data_get($this->from_user, 'name', 'Unknown');
    }
}
