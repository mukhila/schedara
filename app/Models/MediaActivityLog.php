<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'media_file_id', 'user_id', 'action', 'platform', 'response', 'status', 'message',
    ];

    protected function casts(): array
    {
        return ['response' => 'array'];
    }

    public function mediaFile(): BelongsTo { return $this->belongsTo(MediaLibrary::class, 'media_file_id'); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }

    public static function record(
        ?MediaLibrary $file,
        string        $action,
        string        $status = 'success',
        array         $data = [],
        ?string       $message = null
    ): self {
        return static::create([
            'media_file_id' => $file?->id,
            'user_id'       => auth()->id(),
            'action'        => $action,
            'response'      => $data,
            'status'        => $status,
            'message'       => $message,
        ]);
    }
}
