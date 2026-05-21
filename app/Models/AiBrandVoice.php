<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AiBrandVoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'name', 'description', 'industry',
        'tone_attributes', 'brand_keywords', 'example_content',
        'custom_instructions', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'tone_attributes' => 'array',
            'brand_keywords'  => 'array',
            'is_default'      => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());

        // Ensure only one default per tenant
        static::saved(function (self $voice) {
            if ($voice->is_default) {
                static::where('tenant_id', $voice->tenant_id)
                      ->where('id', '!=', $voice->id)
                      ->update(['is_default' => false]);
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function toSystemPromptFragment(): string
    {
        $tones = implode(', ', $this->tone_attributes ?? []);
        $keywords = implode(', ', $this->brand_keywords ?? []);

        $fragment  = "You represent the brand \"{$this->name}\".";
        $fragment .= " Brand tone: {$tones}.";
        if ($keywords) $fragment .= " Key brand terms: {$keywords}.";
        if ($this->industry) $fragment .= " Industry: {$this->industry}.";
        if ($this->custom_instructions) $fragment .= " Additional instructions: {$this->custom_instructions}";
        if ($this->example_content) $fragment .= " Example brand content for reference: \"{$this->example_content}\"";

        return $fragment;
    }
}
