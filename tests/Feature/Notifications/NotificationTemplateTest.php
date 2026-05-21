<?php

namespace Tests\Feature\Notifications;

use App\Models\NotificationTemplate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_create_template_via_api(): void
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/notifications/templates', [
            'template_name'    => 'Post Approved Email',
            'type'             => 'post.approved',
            'channel'          => 'email',
            'subject'          => 'Your post {{post_title}} was approved',
            'message_template' => 'Hi {{user_name}}, your post has been approved and published.',
            'status'           => 'active',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('notification_templates', ['type' => 'post.approved', 'channel' => 'email']);
    }

    public function test_template_render_substitutes_variables(): void
    {
        $template = NotificationTemplate::factory()->create([
            'message_template' => 'Hi {{user_name}}, your post {{post_title}} was approved.',
            'subject'          => 'Post {{post_title}} approved',
        ]);

        $rendered = $template->render(['user_name' => 'Alice', 'post_title' => 'My First Post']);

        $this->assertStringContainsString('Alice', $rendered['body']);
        $this->assertStringContainsString('My First Post', $rendered['subject']);
    }

    public function test_extract_variables_from_template(): void
    {
        $template = NotificationTemplate::factory()->create([
            'message_template' => 'Hi {{user_name}}, workspace {{workspace_name}} updated.',
        ]);

        $vars = $template->extractVariables();

        $this->assertContains('user_name', $vars);
        $this->assertContains('workspace_name', $vars);
    }

    public function test_delete_template(): void
    {
        $this->actingAs($this->user, 'sanctum');

        $template = NotificationTemplate::factory()->create();

        $this->deleteJson('/api/notifications/templates/' . $template->uuid)->assertNoContent();

        $this->assertSoftDeleted('notification_templates', ['id' => $template->id]);
    }
}
