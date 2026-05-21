<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ClientReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'client_workspace_id',
        'report_name',
        'report_type',
        'format',
        'file_url',
        'file_path',
        'report_config',
        'report_data',
        'status',
        'generated_by',
        'generated_at',
        'is_scheduled',
        'schedule_cron',
        'email_delivery',
    ];

    protected function casts(): array
    {
        return [
            'report_config'  => 'array',
            'report_data'    => 'array',
            'generated_at'   => 'datetime',
            'is_scheduled'   => 'boolean',
            'email_delivery' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ─────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(ClientWorkspace::class, 'client_workspace_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
