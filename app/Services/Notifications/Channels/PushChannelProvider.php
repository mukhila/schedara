<?php

namespace App\Services\Notifications\Channels;

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushChannelProvider
{
    private string $projectId;
    private string $serverKey;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id', '');
        $this->serverKey = config('services.fcm.server_key', '');
    }

    public function send(User $user, Notification $notification): void
    {
        $tokens = DeviceToken::where('user_id', $user->id)
            ->orderByDesc('last_active_at')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $title   = $notification->title();
        $body    = $notification->body();
        $payload = [
            'registration_ids' => $tokens,
            'notification'     => [
                'title' => $title,
                'body'  => $body,
                'icon'  => '/favicon.ico',
                'click_action' => $notification->action_url ?? config('app.url'),
            ],
            'data' => [
                'notification_id' => $notification->id,
                'category'        => $notification->category,
                'action_url'      => $notification->action_url ?? '',
            ],
        ];

        try {
            $response = Http::withToken($this->serverKey)
                ->post('https://fcm.googleapis.com/fcm/send', $payload);

            $status = $response->successful() ? 'sent' : 'failed';

            NotificationLog::record(
                notificationId:  $notification->id,
                channel:         'push',
                deliveryStatus:  $status,
                recipient:       implode(',', array_slice($tokens, 0, 3)) . (count($tokens) > 3 ? '...' : ''),
                provider:        'fcm',
                requestPayload:  ['tokens_count' => count($tokens), 'title' => $title],
                responsePayload: $response->json() ?? [],
                errorMessage:    $status === 'failed' ? $response->body() : null,
            );

            if ($status === 'failed') {
                throw new \RuntimeException('FCM push failed: ' . $response->body());
            }

            $this->cleanupInvalidTokens($user->id, $response->json());
        } catch (\Throwable $e) {
            Log::warning('PushChannelProvider failed', ['user' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function cleanupInvalidTokens(int $userId, ?array $responseBody): void
    {
        if (empty($responseBody['results'])) {
            return;
        }

        $tokens = DeviceToken::where('user_id', $userId)->orderByDesc('last_active_at')->get();

        foreach ($responseBody['results'] as $index => $result) {
            if (isset($result['error']) && in_array($result['error'], ['InvalidRegistration', 'NotRegistered'])) {
                $tokens->get($index)?->delete();
            }
        }
    }
}
