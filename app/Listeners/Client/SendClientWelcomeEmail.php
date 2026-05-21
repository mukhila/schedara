<?php

namespace App\Listeners\Client;

use App\Events\Client\ClientCreated;
use App\Jobs\Client\SendOnboardingReminderJob;
use App\Mail\ClientWelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendClientWelcomeEmail implements ShouldQueue
{
    public string $queue = 'emails';

    public function handle(ClientCreated $event): void
    {
        Mail::to($event->client->email, $event->client->client_name)
            ->send(new ClientWelcomeMail($event->client, $event->workspace));

        SendOnboardingReminderJob::dispatch($event->client->id)->delay(now()->addDays(3));
    }
}
