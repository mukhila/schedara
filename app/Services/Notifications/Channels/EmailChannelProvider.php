<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailChannelProvider
{
    public function send(User $user, Notification $notification): void
    {
        if (! $user->email) {
            return;
        }

        $title = $notification->title();
        $body  = $notification->body();

        try {
            Mail::send([], [], function ($message) use ($user, $title, $body) {
                $message->to($user->email, $user->name)
                    ->subject($title)
                    ->html($this->buildHtml($title, $body, $user->name));
            });

            NotificationLog::record(
                notificationId:  $notification->id,
                channel:         'email',
                deliveryStatus:  'sent',
                recipient:       $user->email,
                provider:        config('mail.default', 'smtp'),
                requestPayload:  ['to' => $user->email, 'subject' => $title],
            );
        } catch (\Throwable $e) {
            Log::warning('EmailChannelProvider failed', ['user' => $user->id, 'error' => $e->getMessage()]);

            NotificationLog::record(
                notificationId: $notification->id,
                channel:        'email',
                deliveryStatus: 'failed',
                recipient:      $user->email,
                provider:       config('mail.default', 'smtp'),
                errorMessage:   $e->getMessage(),
            );

            throw $e;
        }
    }

    private function buildHtml(string $title, string $body, string $userName): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px">
          <h2 style="color:#021b2e">{$title}</h2>
          <p style="color:#334155;line-height:1.6">Hi {$userName},</p>
          <p style="color:#334155;line-height:1.6">{$body}</p>
          <hr style="border:none;border-top:1px solid #e3e9ee;margin:24px 0">
          <p style="font-size:12px;color:#94a3b8">Schedara · You are receiving this because you have notifications enabled.</p>
        </body>
        </html>
        HTML;
    }
}
