<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SocialPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'social_account_id', 'page_id', 'page_name', 'page_type',
        'category', 'avatar', 'access_token', 'followers_count', 'metadata', 'is_selected',
    ];

    protected $hidden = ['access_token'];

    protected function casts(): array
    {
        return [
            'access_token'    => 'encrypted',
            'followers_count' => 'integer',
            'metadata'        => 'array',
            'is_selected'     => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($model) => $model->uuid ??= (string) Str::uuid());
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
