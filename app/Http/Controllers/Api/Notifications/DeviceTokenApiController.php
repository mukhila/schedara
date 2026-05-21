<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenApiController extends Controller
{
    /** Register or refresh an FCM device token. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token'   => 'required|string|max:512',
            'device_type' => 'nullable|in:web,android,ios',
            'browser'     => 'nullable|string|max:80',
            'platform'    => 'nullable|string|max:50',
        ]);

        $token = DeviceToken::updateOrCreate(
            ['fcm_token' => $validated['fcm_token']],
            [
                'user_id'        => auth()->id(),
                'device_type'    => $validated['device_type'] ?? 'web',
                'browser'        => $validated['browser'] ?? null,
                'platform'       => $validated['platform'] ?? null,
                'last_active_at' => now(),
            ]
        );

        return response()->json(['uuid' => $token->uuid], 201);
    }

    /** Remove an FCM token (user logged out or revoked permission). */
    public function destroy(string $token): JsonResponse
    {
        DeviceToken::where('user_id', auth()->id())
            ->where('fcm_token', $token)
            ->delete();

        return response()->json(null, 204);
    }
}
