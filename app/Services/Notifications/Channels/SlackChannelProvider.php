<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\SlackIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackChannelProvider
{
    public function send(Notification $notification): void
    {
        if (! $notification->tenant_id) {
            return;
        }

        $integration = SlackIntegration::where('tenant_id', $notification->tenant_id)
            ->where('status', 'active')
            ->first();

        if (! $integration) {
            return;
        }

        $this->sendToWebhook($integration, $notification->title(), $notification->body(), $notification->action_url);
    }

    public function sendToWebhook(SlackIntegration $integration, string $title, string $body, ?string $actionUrl = null): void
    {
        $payload = $this->buildPayload($integration->channel_name, $title, $body, $actionUrl);

        try {
            $response = Http::post($integration->webhook_url, $payload);

            $delivered = $response->successful() && $response->body() === 'ok';

            NotificationLog::record(
                notificationId:  null,
                channel:         'slack',
                deliveryStatus:  $delivered ? 'delivered' : 'failed',
                recipient:       $integration->channel_name,
                provider:        'slack_webhook',
                requestPayload:  ['title' => $title, 'channel' => $integration->channel_name],
                responsePayload: ['body' => $response->body()],
                errorMessage:    $delivered ? null : $response->body(),
            );

            if (! $delivered) {
                throw new \RuntimeException('Slack send failed: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('SlackChannelProvider failed', ['tenant' => $integration->tenant_id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function buildPayload(string $channel, string $title, string $body, ?string $actionUrl): array
    {
        $blocks = [
            [
                'type' => 'header',
                'text' => ['type' => 'plain_text', 'text' => $title, 'emoji' => true],
            ],
            [
                'type' => 'section',
                'text' => ['type' => 'mrkdwn', 'text' => $body],
            ],
        ];

        if ($actionUrl) {
            $blocks[] = [
                'type'     => 'actions',
                'elements' => [[
                    'type'  => 'button',
                    'text'  => ['type' => 'plain_text', 'text' => 'View Details'],
                    'url'   => $actionUrl,
                    'style' => 'primary',
                ]],
            ];
        }

        return ['channel' => $channel, 'blocks' => $blocks];
    }
}
