<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'provider',
        'provider_invoice_id',
        'status',
        'amount',
        'currency',
        'description',
        'hosted_invoice_url',
        'invoice_pdf_url',
        'paid_at',
        'period_start',
        'period_end',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'paid_at'      => 'datetime',
            'period_start' => 'datetime',
            'period_end'   => 'datetime',
            'metadata'     => 'array',
            'amount'       => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Amount in major currency units. */
    public function formattedAmount(): string
    {
        $major = $this->amount / 100;

        return match (strtolower($this->currency)) {
            'inr'   => '₹'.number_format($major, 2),
            default => '$'.number_format($major, 2),
        };
    }
}
