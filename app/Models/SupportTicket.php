<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'user_id', 'assigned_to',
        'ticket_number', 'subject', 'description',
        'priority', 'category', 'status',
        'first_response_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'first_response_at' => 'datetime',
            'resolved_at'       => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            $m->uuid          ??= (string) Str::uuid();
            $m->ticket_number ??= 'TKT-' . strtoupper(Str::random(8));
        });
    }

    public function getRouteKeyName(): string { return 'uuid'; }

    // ── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isOpen(): bool     { return in_array($this->status, ['open', 'in_progress', 'waiting']); }
    public function isResolved(): bool { return in_array($this->status, ['resolved', 'closed']); }

    public function priorityColor(): string
    {
        return match ($this->priority) {
            'critical' => 'coral',
            'high'     => 'gold',
            'medium'   => 'brand',
            default    => 'ink/40',
        };
    }

    public function priorityBadge(): string
    {
        return match ($this->priority) {
            'critical' => 'pill-coral',
            'high'     => 'pill-gold',
            'medium'   => 'pill-brand',
            default    => '',
        };
    }
}
