<?php

namespace App\Jobs\Client;

use App\Models\AgencyClient;
use App\Models\ClientBilling;
use App\Services\Client\ClientBillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int    $clientId,
        public readonly string $eventType,
        public readonly array  $payload = [],
    ) {
        $this->onQueue('billing');
    }

    public function handle(ClientBillingService $billingService): void
    {
        $client = AgencyClient::find($this->clientId);

        if (!$client) {
            return;
        }

        try {
            match ($this->eventType) {
                'subscription.renewed'   => $this->handleRenewal($client, $billingService),
                'subscription.cancelled' => $this->handleCancellation($client),
                'payment.failed'         => $this->handlePaymentFailed($client),
                default                  => Log::warning("Unknown subscription event: {$this->eventType}"),
            };
        } catch (\Throwable $e) {
            Log::error('Subscription processing failed', [
                'client_id'  => $this->clientId,
                'event_type' => $this->eventType,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function handleRenewal(AgencyClient $client, ClientBillingService $service): void
    {
        Log::info("Subscription renewed for client {$client->id}");
    }

    private function handleCancellation(AgencyClient $client): void
    {
        $client->update(['status' => 'inactive']);
        Log::info("Subscription cancelled for client {$client->id}");
    }

    private function handlePaymentFailed(AgencyClient $client): void
    {
        Log::warning("Payment failed for client {$client->id}");
        // Could send reminder email here
    }
}
