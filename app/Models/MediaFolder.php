<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MediaFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'parent_id', 'created_by',
        'name', 'slug', 'path', 'color', 'is_shared',
    ];

    protected function casts(): array
    {
        return ['is_shared' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $folder) {
            $folder->uuid ??= (string) Str::uuid();
            $folder->slug ??= Str::slug($folder->name);
        });
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo    { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function parent(): BelongsTo    { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany    { return $this->hasMany(self::class, 'parent_id'); }
    public function mediaFiles(): HasMany  { return $this->hasMany(MediaLibrary::class, 'folder_id'); }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeRoots($q)                    { return $q->whereNull('parent_id'); }
    public function scopeForTenant($q, int $tenantId) { return $q->where('tenant_id', $tenantId); }

    // ── Helpers ──────────────────────────────────────────────────

    public function getRouteKeyName(): string { return 'uuid'; }

    public function fullPath(): string { return $this->path ?: '/' . $this->name; }

    public function breadcrumbs(): array
    {
        $crumbs   = [];
        $folder   = $this;

        while ($folder) {
            array_unshift($crumbs, ['id' => $folder->uuid, 'name' => $folder->name]);
            $folder = $folder->parent;
        }

        return $crumbs;
    }

    public function allChildIds(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids   = array_merge($ids, $child->allChildIds());
        }
        return $ids;
    }
}
