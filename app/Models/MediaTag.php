<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class MediaTag extends Model
{
    protected $fillable = ['tenant_id', 'tag_name', 'slug', 'color', 'usage_count'];

    protected static function booted(): void
    {
        static::creating(fn ($t) => $t->slug ??= Str::slug($t->tag_name));
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function mediaFiles(): BelongsToMany
    {
        return $this->belongsToMany(MediaLibrary::class, 'media_file_tags', 'media_tag_id', 'media_file_id');
    }

    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }
}
