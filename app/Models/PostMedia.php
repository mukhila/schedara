<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostMedia extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'post_id', 'media_library_id', 'media_type', 'disk',
        'file_path', 'file_url', 'thumbnail_path', 'thumbnail_url',
        'mime_type', 'file_size', 'width', 'height', 'duration',
        'is_watermarked', 'watermark_path', 'sort_order', 'processing_status', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_watermarked'    => 'boolean',
            'file_size'         => 'integer',
            'width'             => 'integer',
            'height'            => 'integer',
            'duration'          => 'decimal:2',
            'sort_order'        => 'integer',
            'metadata'          => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    public function post(): BelongsTo         { return $this->belongsTo(Post::class); }
    public function mediaLibrary(): BelongsTo { return $this->belongsTo(MediaLibrary::class); }

    public function publicUrl(): string
    {
        return $this->file_url ?? Storage::disk($this->disk)->url($this->file_path);
    }

    public function thumbnailUrl(): ?string
    {
        if (! $this->thumbnail_path) return null;
        return $this->thumbnail_url ?? Storage::disk($this->disk)->url($this->thumbnail_path);
    }

    public function isVideo(): bool { return $this->media_type === 'video'; }
    public function isImage(): bool { return $this->media_type === 'image'; }

    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) return round($bytes, 1) . ' ' . $unit;
            $bytes /= 1024;
        }
        return round($bytes, 1) . ' TB';
    }

    public function getRouteKeyName(): string { return 'uuid'; }
}
