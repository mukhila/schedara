<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceApiController extends Controller
{
    public function __construct(private NotificationService $service) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->service->getPreferences($request->user()->id));
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'preferences'             => 'required|array',
            'preferences.*'           => 'array',
            'preferences.*.*'         => 'boolean',
        ]);

        $this->service->updatePreferences($request->user()->id, $request->input('preferences'));

        return response()->json(['message' => 'Preferences saved.']);
    }
}
