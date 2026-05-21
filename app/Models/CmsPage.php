<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CmsPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'title', 'slug', 'content', 'excerpt',
        'meta_title', 'meta_description', 'og_image',
        'page_type', 'status', 'created_by', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            $m->uuid ??= (string) Str::uuid();
            $m->slug ??= Str::slug($m->title);
        });
    }

    public function getRouteKeyName(): string { return 'uuid'; }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(): bool { return $this->status === 'published'; }
    public function isDraft(): bool     { return $this->status === 'draft'; }

    public function publish(): void
    {
        $this->update(['status' => 'published', 'published_at' => now()]);
    }

    public function unpublish(): void
    {
        $this->update(['status' => 'draft', 'published_at' => null]);
    }
}
