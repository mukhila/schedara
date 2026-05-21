<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'notification_id', 'channel', 'recipient', 'provider',
        'request_payload', 'response_payload',
        'delivery_status', 'error_message', 'attempts', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload'  => 'array',
            'response_payload' => 'array',
            'delivered_at'     => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function isDelivered(): bool
    {
        return in_array($this->delivery_status, ['delivered', 'sent']);
    }

    public function isFailed(): bool
    {
        return in_array($this->delivery_status, ['failed', 'bounced']);
    }

    public static function record(
        ?int    $notificationId,
        string  $channel,
        string  $deliveryStatus,
        ?string $recipient       = null,
        ?string $provider        = null,
        array   $requestPayload  = [],
        array   $responsePayload = [],
        ?string $errorMessage    = null,
    ): self {
        return static::create([
            'notification_id'  => $notificationId,
            'channel'          => $channel,
            'recipient'        => $recipient,
            'provider'         => $provider,
            'request_payload'  => $requestPayload ?: null,
            'response_payload' => $responsePayload ?: null,
            'delivery_status'  => $deliveryStatus,
            'error_message'    => $errorMessage,
            'delivered_at'     => in_array($deliveryStatus, ['delivered', 'sent']) ? now() : null,
        ]);
    }
}
