<?php

namespace Tests\Feature\Notifications;

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\Channels\PushChannelProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_token_registration(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/notifications/device-token', [
            'fcm_token'   => 'test-fcm-token-abc123',
            'device_type' => 'web',
            'browser'     => 'Chrome',
            'platform'    => 'Windows',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('device_tokens', [
            'user_id'   => $user->id,
            'fcm_token' => 'test-fcm-token-abc123',
        ]);
    }

    public function test_duplicate_token_upserted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        DeviceToken::factory()->create(['user_id' => $user->id, 'fcm_token' => 'same-token']);

        $this->postJson('/api/notifications/device-token', ['fcm_token' => 'same-token']);

        $this->assertCount(1, DeviceToken::where('fcm_token', 'same-token')->get());
    }

    public function test_device_token_deleted(): void
    {
        $user  = User::factory()->create();
        $token = DeviceToken::factory()->create(['user_id' => $user->id, 'fcm_token' => 'remove-me']);
        $this->actingAs($user, 'sanctum');

        $this->deleteJson('/api/notifications/device-token/remove-me')->assertNoContent();

        $this->assertDatabaseMissing('device_tokens', ['fcm_token' => 'remove-me']);
    }

    public function test_push_provider_sends_to_fcm(): void
    {
        Http::fake(['https://fcm.googleapis.com/*' => Http::response(['success' => 1, 'results' => [['message_id' => 'x']]], 200)]);

        $user  = User::factory()->create();
        DeviceToken::factory()->create(['user_id' => $user->id, 'fcm_token' => 'valid-token']);

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'data'    => ['title' => 'Test', 'body' => 'Hello'],
        ]);

        app(PushChannelProvider::class)->send($user, $notification);

        Http::assertSent(fn ($req) => str_contains($req->url(), 'fcm.googleapis.com'));
    }
}
