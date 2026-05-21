<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationContact extends Model
{
    protected $fillable = ['user_id', 'phone_number', 'whatsapp_number'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasPhone(): bool
    {
        return ! empty($this->phone_number);
    }

    public function hasWhatsApp(): bool
    {
        return ! empty($this->whatsapp_number);
    }
}
