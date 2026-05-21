<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentApproval extends Model
{
    protected $fillable = [
        'media_file_id', 'requested_by', 'approved_by',
        'status', 'comments', 'approved_at',
    ];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function mediaFile(): BelongsTo  { return $this->belongsTo(MediaLibrary::class, 'media_file_id'); }
    public function requester(): BelongsTo  { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver(): BelongsTo   { return $this->belongsTo(User::class, 'approved_by'); }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }

    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopeRejected($q) { return $q->where('status', 'rejected'); }
}
