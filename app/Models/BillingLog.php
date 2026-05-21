<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'action', 'gateway',
        'payload', 'response', 'status', 'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'    => 'array',
            'response'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    public static function record(
        string   $action,
        array    $payload  = [],
        array    $response = [],
        string   $status   = 'success',
        ?string  $gateway  = null,
        ?int     $tenantId = null
    ): self {
        return static::create([
            'tenant_id'  => $tenantId,
            'action'     => $action,
            'gateway'    => $gateway,
            'payload'    => $payload,
            'response'   => $response,
            'status'     => $status,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
