<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserApiController extends Controller
{
    public function __construct(private AdminUserService $users) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->users->paginate($request->only(['search', 'status']), 20);

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['tenants']);
        $user->loadCount(['tenants']);

        return response()->json($user);
    }

    public function suspend(Request $request, User $user): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $this->users->suspend($user, $request->input('reason', ''));

        return response()->json(['message' => 'User suspended.']);
    }

    public function activate(User $user): JsonResponse
    {
        $this->users->activate($user);

        return response()->json(['message' => 'User activated.']);
    }
}
