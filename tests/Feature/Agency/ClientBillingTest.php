<?php

namespace Tests\Feature\Agency;

use App\Models\AgencyClient;
use App\Models\ClientBilling;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientBillingTest extends TestCase
{
    use RefreshDatabase;

    private User        $user;
    private Tenant      $agency;
    private AgencyClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user   = User::factory()->create();
        $this->agency = Tenant::factory()->create();
        $this->client = AgencyClient::factory()->create(['agency_id' => $this->agency->id]);

        $this->app->instance('tenant', $this->agency);
    }

    public function test_can_create_invoice(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/clients/{$this->client->uuid}/billing", [
                'subscription_plan' => 'Growth ($249/mo)',
                'amount'            => 24900,
                'tax'               => 0,
                'currency'          => 'USD',
            ]);

        $response->assertCreated()
                 ->assertJsonPath('invoice.payment_status', 'open');

        $this->assertDatabaseHas('client_billing', [
            'agency_client_id'  => $this->client->id,
            'subscription_plan' => 'Growth ($249/mo)',
        ]);
    }

    public function test_can_mark_invoice_as_paid(): void
    {
        $invoice = ClientBilling::factory()->create([
            'agency_client_id' => $this->client->id,
            'payment_status'   => 'open',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/clients/billing/{$invoice->uuid}/mark-paid");

        $response->assertOk()
                 ->assertJsonPath('invoice.payment_status', 'paid');
    }

    public function test_invoice_number_is_unique(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/clients/{$this->client->uuid}/billing", [
                'subscription_plan' => 'Starter',
                'amount'            => 9900,
            ]);
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/clients/{$this->client->uuid}/billing", [
                'subscription_plan' => 'Starter',
                'amount'            => 9900,
            ]);

        $invoices = ClientBilling::pluck('invoice_number')->toArray();
        $this->assertCount(count($invoices), array_unique($invoices));
    }
}
