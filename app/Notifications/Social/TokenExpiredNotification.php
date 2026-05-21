<?php

namespace App\Notifications\Social;

use App\Models\SocialAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TokenExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly SocialAccount $account) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $platform = $this->account->platform?->name ?? 'Social';

        return (new MailMessage)
            ->subject("{$platform} account needs reconnection — Schedara")
            ->line("Your {$platform} account \"{$this->account->account_name}\" has expired and scheduled posts may fail.")
            ->action('Reconnect Account', route('social.index'))
            ->line('Reconnect takes less than 30 seconds.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'social_token_expired',
            'account_id'   => $this->account->id,
            'account_name' => $this->account->account_name,
            'platform'     => $this->account->platform?->slug,
        ];
    }
}
