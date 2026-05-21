<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibrary extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $table = 'media_library';

    protected $fillable = [
        'uuid', 'tenant_id', 'user_id', 'folder_id',
        'name', 'original_name', 'disk',
        'url', 's3_key', 'thumbnail_path', 'thumbnail_url',
        'mime_type', 'extension', 'file_hash', 'type',
        'size', 'width', 'height', 'duration',
        'alt_text', 'metadata', 'tags',
        'optimization_status', 'compression_status', 'approval_status',
        'version', 'is_favorite', 'share_token', 'share_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'tags'             => 'array',
            'metadata'         => 'array',
            'size'             => 'integer',
            'width'            => 'integer',
            'height'           => 'integer',
            'duration'         => 'integer',
            'is_favorite'      => 'boolean',
            'share_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ────────────────────────────────────────────

    public function uploader(): BelongsTo        { return $this->belongsTo(User::class, 'user_id'); }
    public function folder(): BelongsTo          { return $this->belongsTo(MediaFolder::class, 'folder_id'); }
    public function approval(): HasOne           { return $this->hasOne(ContentApproval::class, 'media_file_id')->latestOfMany(); }
    public function approvals(): HasMany         { return $this->hasMany(ContentApproval::class, 'media_file_id'); }
    public function versions(): HasMany          { return $this->hasMany(MediaVersion::class, 'media_file_id')->orderByDesc('version'); }
    public function activityLogs(): HasMany      { return $this->hasMany(MediaActivityLog::class, 'media_file_id'); }

    public function mediaTags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_file_tags', 'media_file_id', 'media_tag_id');
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeImages($q)                   { return $q->where('type', 'image'); }
    public function scopeVideos($q)                   { return $q->where('type', 'video'); }
    public function scopeDocuments($q)                { return $q->where('type', 'document'); }
    public function scopeFavorites($q)                { return $q->where('is_favorite', true); }
    public function scopeInFolder($q, ?int $folderId) { return $q->where('folder_id', $folderId); }
    public function scopePendingApproval($q)          { return $q->where('approval_status', 'pending'); }
    public function scopeApproved($q)                 { return $q->where('approval_status', 'approved'); }

    public function scopeSearch($q, string $term)
    {
        return $q->where(fn ($sub) =>
            $sub->where('name', 'like', "%{$term}%")
                ->orWhere('original_name', 'like', "%{$term}%")
                ->orWhere('alt_text', 'like', "%{$term}%")
        );
    }

    // ── URL Helpers ──────────────────────────────────────────────

    public function publicUrl(): string
    {
        return $this->url ?: Storage::disk($this->disk ?? 'local')->url($this->s3_key);
    }

    public function thumbnailPublicUrl(): ?string
    {
        if (!$this->thumbnail_path) return null;
        return $this->thumbnail_url ?: Storage::disk($this->disk ?? 'local')->url($this->thumbnail_path);
    }

    public function previewUrl(): string
    {
        return $this->thumbnailPublicUrl() ?? $this->publicUrl();
    }

    public function shareUrl(): ?string
    {
        if (!$this->share_token) return null;
        if ($this->share_expires_at && $this->share_expires_at->isPast()) return null;
        return route('cms.share', $this->share_token);
    }

    // ── Type Helpers ─────────────────────────────────────────────

    public function isImage(): bool    { return $this->type === 'image'; }
    public function isVideo(): bool    { return $this->type === 'video'; }
    public function isDocument(): bool { return $this->type === 'document'; }
    public function isAudio(): bool    { return $this->type === 'audio'; }

    public function humanSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = $this->size ?? 0;
        $i     = 0;
        while ($size >= 1024 && $i < count($units) - 1) { $size /= 1024; $i++; }
        return round($size, 1) . ' ' . $units[$i];
    }

    public function humanDuration(): ?string
    {
        if (!$this->duration) return null;
        $m = floor($this->duration / 60);
        $s = $this->duration % 60;
        return sprintf('%d:%02d', $m, $s);
    }

    public function getRouteKeyName(): string { return 'uuid'; }

    public function approvalStatusColor(): string
    {
        return match ($this->approval_status) {
            'approved' => 'text-mint',
            'rejected' => 'text-coral',
            'pending'  => 'text-gold',
            'archived' => 'text-ink/40',
            default    => 'text-ink/60',
        };
    }

    public function optimizationDone(): bool { return $this->optimization_status === 'done'; }
    public function compressionDone(): bool  { return $this->compression_status === 'done'; }
}
