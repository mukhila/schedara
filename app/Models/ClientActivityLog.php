<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'client_workspace_id',
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'properties',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(ClientWorkspace::class, 'client_workspace_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
