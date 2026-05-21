<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPlatform extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon', 'color', 'status', 'scopes', 'capabilities',
    ];

    protected $casts = [
        'status'       => 'boolean',
        'scopes'       => 'array',
        'capabilities' => 'array',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class, 'platform_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    /** CSS color class for the platform icon chip */
    public function getColorClassAttribute(): string
    {
        return match ($this->slug) {
            'facebook'  => 'bg-blue-600',
            'instagram' => 'bg-gradient-to-br from-purple-600 via-pink-600 to-orange-400',
            'linkedin'  => 'bg-sky-700',
            'twitter'   => 'bg-black',
            'pinterest' => 'bg-red-600',
            'youtube'   => 'bg-red-500',
            'threads'   => 'bg-black',
            default     => 'bg-gray-500',
        };
    }
}
