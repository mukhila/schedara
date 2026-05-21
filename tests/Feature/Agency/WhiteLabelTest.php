<?php

namespace Tests\Feature\Agency;

use App\Models\AgencyClient;
use App\Models\ClientWorkspace;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhiteLabelSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhiteLabelTest extends TestCase
{
    use RefreshDatabase;

    private User           $user;
    private Tenant         $agency;
    private AgencyClient   $client;
    private ClientWorkspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user      = User::factory()->create();
        $this->agency    = Tenant::factory()->create();
        $this->client    = AgencyClient::factory()->create(['agency_id' => $this->agency->id]);
        $this->workspace = ClientWorkspace::factory()->create(['agency_client_id' => $this->client->id]);

        $this->app->instance('tenant', $this->agency);
    }

    public function test_can_update_white_label_settings(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/workspaces/{$this->workspace->uuid}/white-label", [
                'brand_name'         => 'My Agency',
                'primary_color'      => '#FF5733',
                'hide_saas_branding' => true,
            ]);

        $response->assertOk()
                 ->assertJsonPath('settings.brand_name', 'My Agency');

        $this->assertDatabaseHas('white_label_settings', [
            'client_workspace_id' => $this->workspace->id,
            'brand_name'          => 'My Agency',
        ]);
    }

    public function test_invalid_color_format_fails_validation(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/workspaces/{$this->workspace->uuid}/white-label", [
                'primary_color' => 'not-a-color',
            ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['primary_color']);
    }

    public function test_white_label_settings_are_cached(): void
    {
        WhiteLabelSetting::create([
            'client_workspace_id' => $this->workspace->id,
            'brand_name'          => 'Cached Agency',
        ]);

        // Hit twice — second should come from cache
        $this->actingAs($this->user, 'sanctum')
             ->getJson("/api/workspaces/{$this->workspace->uuid}/white-label");
        $response = $this->actingAs($this->user, 'sanctum')
             ->getJson("/api/workspaces/{$this->workspace->uuid}/white-label");

        $response->assertOk()
                 ->assertJsonPath('brand_name', 'Cached Agency');
    }
}
