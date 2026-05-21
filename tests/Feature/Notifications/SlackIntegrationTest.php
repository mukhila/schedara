<?php

namespace Tests\Feature\Notifications;

use App\Models\SlackIntegration;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SlackIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user   = User::factory()->create();
        $this->tenant = Tenant::factory()->create(['owner_id' => $this->user->id]);
    }

    public function test_connect_saves_integration(): void
    {
        $this->actingAs($this->user);
        app()->instance('current.tenant', $this->tenant);

        $response = $this->post(route('notifications.slack.connect'), [
            'webhook_url'  => 'https://hooks.slack.com/services/T000/B000/xxxx',
            'channel_name' => '#alerts',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('slack_integrations', [
            'tenant_id'    => $this->tenant->id,
            'channel_name' => '#alerts',
        ]);
    }

    public function test_test_message_calls_webhook(): void
    {
        Http::fake(['https://hooks.slack.com/*' => Http::response('ok', 200)]);

        $this->actingAs($this->user);
        app()->instance('current.tenant', $this->tenant);

        SlackIntegration::create([
            'tenant_id'   => $this->tenant->id,
            'webhook_url' => 'https://hooks.slack.com/services/T000/B000/xxxx',
            'channel_name' => '#alerts',
            'status'      => 'active',
        ]);

        $this->post(route('notifications.slack.test'))->assertRedirect();

        Http::assertSent(fn ($req) => str_contains($req->url(), 'hooks.slack.com'));
    }

    public function test_disconnect_soft_deletes(): void
    {
        $this->actingAs($this->user);
        app()->instance('current.tenant', $this->tenant);

        SlackIntegration::create([
            'tenant_id'    => $this->tenant->id,
            'webhook_url'  => 'https://hooks.slack.com/services/T000/B000/xxxx',
            'channel_name' => '#alerts',
            'status'       => 'active',
        ]);

        $this->delete(route('notifications.slack.disconnect'))->assertRedirect();

        $this->assertSoftDeleted('slack_integrations', ['tenant_id' => $this->tenant->id]);
    }
}
