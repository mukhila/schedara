<?php

namespace App\Jobs\Client;

use App\Mail\OnboardingReminderMail;
use App\Models\AgencyClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOnboardingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $clientId,
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        $client = AgencyClient::with('onboardingSteps')->find($this->clientId);

        if (! $client || $client->status !== 'onboarding') {
            return;
        }

        $pending = $client->onboardingSteps
            ->whereNotIn('status', ['completed', 'skipped'])
            ->count();

        if ($pending === 0) {
            return;
        }

        try {
            Mail::to($client->email, $client->client_name)
                ->send(new OnboardingReminderMail($client, $pending));
        } catch (\Throwable $e) {
            Log::error('Onboarding reminder failed', [
                'client_id' => $this->clientId,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
