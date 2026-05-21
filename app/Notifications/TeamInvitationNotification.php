<?php

namespace App\Notifications;

use App\Models\TeamInvitation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly TeamInvitation $invitation,
        private readonly User           $inviter,
        private readonly Tenant         $tenant,
        private readonly string         $signedUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $role     = $this->invitation->roleEnum()->label();
        $expires  = $this->invitation->expires_at->diffForHumans();

        $mail = (new MailMessage)
            ->subject("{$this->inviter->name} invited you to join {$this->tenant->name} on Schedara")
            ->greeting("You've been invited!")
            ->line("{$this->inviter->name} has invited you to join **{$this->tenant->name}** as **{$role}**.")
            ->action('Accept invitation', $this->signedUrl)
            ->line("This invitation expires {$expires}.")
            ->line('If you were not expecting this invitation, you can ignore this email.');

        if ($this->invitation->message) {
            $mail->line("**Message from {$this->inviter->name}:** {$this->invitation->message}");
        }

        return $mail;
    }
}
