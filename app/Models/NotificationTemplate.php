<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'tenant_id', 'template_name', 'type', 'channel',
        'subject', 'message_template', 'variables', 'status',
    ];

    protected function casts(): array
    {
        return ['variables' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function render(array $variables): array
    {
        $subject = $this->subject ?? '';
        $body    = $this->message_template;

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject     = str_replace($placeholder, (string) $value, $subject);
            $body        = str_replace($placeholder, (string) $value, $body);
        }

        return ['subject' => $subject, 'body' => $body];
    }

    /** Declared variable names extracted from the template body. */
    public function extractVariables(): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $this->message_template, $matches);

        return array_unique($matches[1] ?? []);
    }
}
