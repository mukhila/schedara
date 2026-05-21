<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhiteLabelSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_workspace_id',
        'brand_name',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'accent_color',
        'custom_domain',
        'domain_verified',
        'email_settings',
        'login_background',
        'support_email',
        'support_url',
        'hide_saas_branding',
        'custom_css',
        'social_links',
    ];

    protected function casts(): array
    {
        return [
            'email_settings'    => 'array',
            'custom_css'        => 'array',
            'social_links'      => 'array',
            'domain_verified'   => 'boolean',
            'hide_saas_branding'=> 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(ClientWorkspace::class, 'client_workspace_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function cssVariables(): string
    {
        return implode(';', [
            "--primary: {$this->primary_color}",
            "--secondary: {$this->secondary_color}",
            "--accent: {$this->accent_color}",
        ]);
    }
}
