<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ClientBilling extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'client_billing';

    protected $fillable = [
        'uuid',
        'agency_client_id',
        'invoice_number',
        'subscription_plan',
        'provider',
        'provider_invoice_id',
        'amount',
        'tax',
        'total',
        'currency',
        'payment_status',
        'due_date',
        'paid_at',
        'line_items',
        'meta',
        'notes',
        'pdf_url',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'integer',
            'tax'        => 'integer',
            'total'      => 'integer',
            'due_date'   => 'date',
            'paid_at'    => 'datetime',
            'line_items' => 'array',
            'meta'       => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    // ── Relationships ─────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(AgencyClient::class, 'agency_client_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->payment_status !== 'paid' && $this->due_date?->isPast();
    }

    public function formattedTotal(): string
    {
        $major = $this->total / 100;

        return match (strtolower($this->currency)) {
            'inr'   => '₹' . number_format($major, 2),
            'eur'   => '€' . number_format($major, 2),
            'gbp'   => '£' . number_format($major, 2),
            default => '$' . number_format($major, 2),
        };
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
