<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientOnboarding extends Model
{
    use HasFactory;

    protected $table = 'client_onboarding';

    protected $fillable = [
        'agency_client_id',
        'onboarding_step',
        'status',
        'step_order',
        'step_data',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'step_data'    => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(AgencyClient::class, 'agency_client_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
