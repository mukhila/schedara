<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PostApproval extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'post_id', 'requested_by', 'approved_by',
        'status', 'request_comment', 'reviewer_comment', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
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

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }

    public function approve(int $reviewerId, ?string $comment = null): void
    {
        $this->update([
            'status'           => 'approved',
            'approved_by'      => $reviewerId,
            'reviewer_comment' => $comment,
            'reviewed_at'      => now(),
        ]);
        $this->post->update(['status' => Post::STATUS_APPROVED]);
    }

    public function reject(int $reviewerId, string $reason): void
    {
        $this->update([
            'status'           => 'rejected',
            'approved_by'      => $reviewerId,
            'reviewer_comment' => $reason,
            'reviewed_at'      => now(),
        ]);
        $this->post->update(['status' => Post::STATUS_DRAFT]);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForReviewer($query, int $userId)
    {
        return $query->whereHas('post', fn ($q) => $q->where('user_id', '!=', $userId));
    }
}
