<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\User;
use App\Models\UserNotificationContact;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannelProvider
{
    private string $sid;
    private string $token;
    private string $from;

    public function __construct()
    {
        $this->sid   = config('services.twilio.sid', '');
        $this->token = config('services.twilio.token', '');
        $this->from  = config('services.twilio.sms_from', '');
    }

    public function send(User $user, Notification $notification): void
    {
        $contact = UserNotificationContact::where('user_id', $user->id)->first();

        if (! $contact || ! $contact->hasPhone()) {
            return;
        }

        $to      = $contact->phone_number;
        $message = $notification->title() . ': ' . $notification->body();
        $message = mb_strimwidth($message, 0, 160); // GSM-7 single SMS limit

        try {
            $response = Http::withBasicAuth($this->sid, $this->token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json", [
                    'To'   => $to,
                    'From' => $this->from,
                    'Body' => $message,
                ]);

            $status = $response->successful() ? 'sent' : 'failed';

            NotificationLog::record(
                notificationId:  $notification->id,
                channel:         'sms',
                deliveryStatus:  $status,
                recipient:       $to,
                provider:        'twilio_sms',
                requestPayload:  ['to' => $to, 'body_length' => strlen($message)],
                responsePayload: $response->json() ?? [],
                errorMessage:    $status === 'failed' ? ($response->json()['message'] ?? $response->body()) : null,
            );

            if ($status === 'failed') {
                throw new \RuntimeException('SMS send failed: ' . ($response->json()['message'] ?? $response->body()));
            }
        } catch (\Throwable $e) {
            Log::warning('SmsChannelProvider failed', ['user' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
