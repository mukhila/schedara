<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationContactApiController extends Controller
{
    public function show(): JsonResponse
    {
        $contact = UserNotificationContact::firstOrNew(['user_id' => auth()->id()]);

        return response()->json([
            'phone_number'     => $contact->phone_number,
            'whatsapp_number'  => $contact->whatsapp_number,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number'    => 'nullable|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
        ]);

        UserNotificationContact::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated,
        );

        return response()->json(['success' => true]);
    }
}
