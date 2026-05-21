<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class SessionController extends Controller
{
    public function index(Request $request): View
    {
        $tokens = PersonalAccessToken::where('tokenable_id', $request->user()->id)
            ->where('tokenable_type', get_class($request->user()))
            ->orderByDesc('last_used_at')
            ->get();

        return view('auth.sessions', compact('tokens'));
    }

    public function revoke(Request $request, int $tokenId): RedirectResponse|JsonResponse
    {
        PersonalAccessToken::where('tokenable_id', $request->user()->id)
            ->where('id', $tokenId)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Session revoked.']);
        }

        return back()->with('success', 'Session revoked successfully.');
    }

    public function revokeAll(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->tokens()->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'All sessions revoked.']);
        }

        return back()->with('success', 'All API sessions have been revoked.');
    }
}
