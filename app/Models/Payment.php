<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'uuid', 'tenant_id', 'invoice_id',
        'payment_gateway', 'transaction_id',
        'amount', 'currency',
        'payment_status', 'gateway_response',
        'failure_reason', 'retry_count', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'integer',
            'retry_count'      => 'integer',
            'paid_at'          => 'datetime',
            'gateway_response' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ── Relationships ─────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->payment_status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    public function formattedAmount(): string
    {
        $major = $this->amount / 100;

        return match (strtolower($this->currency)) {
            'inr'   => '₹' . number_format($major, 2),
            'eur'   => '€' . number_format($major, 2),
            'gbp'   => '£' . number_format($major, 2),
            default => '$' . number_format($major, 2),
        };
    }
}
