<?php

namespace Tests\Feature\Agency;

use App\Models\AgencyClient;
use App\Models\ClientWorkspace;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyClientTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private Tenant $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user   = User::factory()->create();
        $this->agency = Tenant::factory()->create();

        // Bind tenant into container for middleware simulation
        $this->app->instance('tenant', $this->agency);
    }

    public function test_can_list_clients(): void
    {
        AgencyClient::factory(3)->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => $this->agency->id])
            ->getJson('/api/clients');

        $response->assertOk()
                 ->assertJsonStructure(['data', 'total']);
    }

    public function test_can_create_client(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => $this->agency->id])
            ->postJson('/api/clients', [
                'client_name'  => 'Acme Corp',
                'email'        => 'acme@example.com',
                'company_name' => 'Acme Corporation',
                'timezone'     => 'UTC',
            ]);

        $response->assertCreated()
                 ->assertJsonPath('client.client_name', 'Acme Corp');

        $this->assertDatabaseHas('agency_clients', [
            'client_name' => 'Acme Corp',
            'agency_id'   => $this->agency->id,
        ]);
    }

    public function test_create_client_requires_name_and_email(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => $this->agency->id])
            ->postJson('/api/clients', []);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['client_name', 'email']);
    }

    public function test_can_update_client(): void
    {
        $client = AgencyClient::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => $this->agency->id])
            ->putJson("/api/clients/{$client->uuid}", [
                'client_name' => 'Updated Name',
            ]);

        $response->assertOk()
                 ->assertJsonPath('client.client_name', 'Updated Name');
    }

    public function test_can_delete_client(): void
    {
        $client = AgencyClient::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => $this->agency->id])
            ->deleteJson("/api/clients/{$client->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('agency_clients', ['id' => $client->id]);
    }

    public function test_cannot_access_another_agencys_client(): void
    {
        $otherAgency = Tenant::factory()->create();
        $client      = AgencyClient::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => $this->agency->id])
            ->getJson("/api/clients/{$client->uuid}");

        $response->assertNotFound();
    }

    public function test_client_creates_workspace_and_onboarding_steps(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => $this->agency->id])
            ->postJson('/api/clients', [
                'client_name' => 'Test Client',
                'email'       => 'test@example.com',
            ]);

        $response->assertCreated();

        $client = AgencyClient::where('email', 'test@example.com')->first();

        $this->assertNotNull($client);
        $this->assertDatabaseHas('client_workspaces', ['agency_client_id' => $client->id]);
        $this->assertDatabaseCount('client_onboarding', 6);
    }
}
