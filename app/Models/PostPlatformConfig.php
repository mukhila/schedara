<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostPlatformConfig extends Model
{
    protected $fillable = [
        'post_id', 'social_account_id', 'platform',
        'content_override', 'media_override', 'first_comment',
        'status', 'platform_post_id', 'response_data', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'media_override' => 'array',
            'response_data'  => 'array',
            'published_at'   => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function post(): BelongsTo          { return $this->belongsTo(Post::class); }
    public function socialAccount(): BelongsTo { return $this->belongsTo(SocialAccount::class); }

    // ── Helpers ──────────────────────────────────────────────────

    /** Effective caption — override takes precedence over parent post content. */
    public function effectiveContent(): string
    {
        return $this->content_override ?? $this->post->content;
    }

    public function markPublished(string $platformPostId): void
    {
        $this->update([
            'status'           => 'published',
            'platform_post_id' => $platformPostId,
        ]);
    }
}
