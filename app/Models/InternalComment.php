<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InternalComment extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'user_id', 'post_id', 'task_id',
        'parent_id', 'comment', 'attachments', 'mentions', 'reactions',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'mentions'    => 'array',
            'reactions'   => 'array',
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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(CollaborationTask::class, 'task_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }

    public function addReaction(int $userId, string $emoji): void
    {
        $reactions = $this->reactions ?? [];
        $reactions[$emoji] = array_unique(array_merge($reactions[$emoji] ?? [], [$userId]));
        $this->update(['reactions' => $reactions]);
    }

    public function removeReaction(int $userId, string $emoji): void
    {
        $reactions = $this->reactions ?? [];
        if (isset($reactions[$emoji])) {
            $reactions[$emoji] = array_values(array_filter($reactions[$emoji], fn ($id) => $id !== $userId));
            if (empty($reactions[$emoji])) {
                unset($reactions[$emoji]);
            }
        }
        $this->update(['reactions' => $reactions]);
    }
}
