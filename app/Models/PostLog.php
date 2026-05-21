<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'post_id', 'user_id', 'action', 'platform', 'response', 'status', 'message',
    ];

    protected function casts(): array
    {
        return ['response' => 'array'];
    }

    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function record(Post $post, string $action, string $status = 'success', array $data = [], ?string $platform = null, ?string $message = null): self
    {
        return static::create([
            'post_id'  => $post->id,
            'user_id'  => auth()->id(),
            'action'   => $action,
            'platform' => $platform,
            'response' => $data,
            'status'   => $status,
            'message'  => $message,
        ]);
    }
}
