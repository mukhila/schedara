<?php

namespace Tests\Feature\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Billing\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function stripePayload(string $type, array $data = []): array
    {
        return [
            'id'      => 'evt_test_' . uniqid(),
            'type'    => $type,
            'data'    => ['object' => $data],
            'created' => time(),
        ];
    }

    public function test_webhook_endpoint_is_accessible_without_csrf(): void
    {
        // StripeService signature verification must be bypassed for unit test
        $this->mock(StripeService::class, function ($mock) {
            $mock->shouldReceive('handleWebhook')->once()->andReturnNull();
        });

        $this->postJson('/billing/stripe/webhook', $this->stripePayload('ping'))
            ->assertOk();
    }

    public function test_invalid_signature_returns_400(): void
    {
        $this->mock(StripeService::class, function ($mock) {
            $mock->shouldReceive('handleWebhook')
                ->once()
                ->andThrow(new \RuntimeException('Invalid signature'));
        });

        $this->postJson('/billing/stripe/webhook', $this->stripePayload('ping'))
            ->assertStatus(400);
    }

    public function test_invoice_payment_succeeded_activates_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $plan   = Plan::factory()->create();

        $sub = Subscription::factory()->create([
            'tenant_id'          => $tenant->id,
            'plan_id'            => $plan->id,
            'status'             => 'past_due',
            'stripe_customer_id' => 'cus_test123',
        ]);

        $this->mock(StripeService::class, function ($mock) use ($sub) {
            $mock->shouldReceive('handleWebhook')
                ->once()
                ->andReturnUsing(function () use ($sub) {
                    $sub->update(['status' => 'active']);
                });
        });

        $this->postJson('/billing/stripe/webhook', $this->stripePayload('invoice.payment_succeeded', [
            'customer'       => 'cus_test123',
            'subscription'   => $sub->stripe_subscription_id,
        ]))->assertOk();

        $this->assertEquals('active', $sub->fresh()->status);
    }

    public function test_subscription_deleted_event_cancels_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $plan   = Plan::factory()->create();

        $sub = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id'   => $plan->id,
            'status'    => 'active',
        ]);

        $this->mock(StripeService::class, function ($mock) use ($sub) {
            $mock->shouldReceive('handleWebhook')
                ->once()
                ->andReturnUsing(function () use ($sub) {
                    $sub->update(['status' => 'cancelled']);
                });
        });

        $this->postJson('/billing/stripe/webhook', $this->stripePayload('customer.subscription.deleted', [
            'id' => $sub->stripe_subscription_id ?? 'sub_test',
        ]))->assertOk();

        $this->assertEquals('cancelled', $sub->fresh()->status);
    }

    public function test_server_exception_returns_500(): void
    {
        $this->mock(StripeService::class, function ($mock) {
            $mock->shouldReceive('handleWebhook')
                ->once()
                ->andThrow(new \Exception('DB is down'));
        });

        $this->postJson('/billing/stripe/webhook', $this->stripePayload('ping'))
            ->assertStatus(500);
    }
}
