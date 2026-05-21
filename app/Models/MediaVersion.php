<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaVersion extends Model
{
    protected $fillable = [
        'media_file_id', 'created_by', 'version', 'file_path', 'file_url', 'file_size', 'change_note',
    ];

    public function mediaFile(): BelongsTo { return $this->belongsTo(MediaLibrary::class, 'media_file_id'); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
}
