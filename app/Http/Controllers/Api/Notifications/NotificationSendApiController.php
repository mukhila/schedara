<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSendApiController extends Controller
{
    public function __construct(
        private readonly NotificationService $service,
    ) {}

    /**
     * Programmatically send a notification.
     * Intended for internal API-to-API calls or super-admin dispatch.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'type'       => 'required|string|max:80',
            'category'   => 'required|string|max:30',
            'title'      => 'required|string|max:200',
            'body'       => 'required|string',
            'action_url' => 'nullable|url',
            'priority'   => 'nullable|in:normal,high,critical',
            'payload'    => 'nullable|array',
        ]);

        $user   = User::findOrFail($validated['user_id']);
        $tenant = app()->bound('current.tenant') ? app('current.tenant') : null;

        $notification = $this->service->send(
            user:      $user,
            type:      $validated['type'],
            category:  $validated['category'],
            title:     $validated['title'],
            body:      $validated['body'],
            payload:   $validated['payload'] ?? [],
            actionUrl: $validated['action_url'] ?? null,
            priority:  $validated['priority'] ?? 'normal',
            tenantId:  $tenant?->id,
        );

        return response()->json([
            'success'         => true,
            'notification_id' => $notification?->id,
        ], 201);
    }
}
