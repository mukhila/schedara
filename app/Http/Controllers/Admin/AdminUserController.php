<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private AdminUserService $users) {}

    public function index(Request $request): View
    {
        $users = $this->users->paginate($request->only(['search', 'status']));

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['tenants', 'notificationPreferences']);

        return view('admin.users.show', compact('user'));
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $this->users->suspend($user, $request->input('reason', ''));

        return back()->with('success', "User {$user->email} suspended.");
    }

    public function activate(User $user): RedirectResponse
    {
        $this->users->activate($user);

        return back()->with('success', "User {$user->email} activated.");
    }

    public function makeAdmin(User $user): RedirectResponse
    {
        $this->users->makeAdmin($user);

        return back()->with('success', "Super-admin granted to {$user->email}.");
    }

    public function revokeAdmin(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot revoke your own admin access.');
        }

        $this->users->revokeAdmin($user);

        return back()->with('success', "Super-admin revoked from {$user->email}.");
    }

    public function impersonate(User $user): RedirectResponse
    {
        $this->users->impersonate($user);

        return redirect()->route('dashboard')->with('info', "Now acting as {$user->email}.");
    }

    public function stopImpersonating(): RedirectResponse
    {
        $this->users->stopImpersonating();

        return redirect()->route('admin.dashboard')->with('info', 'Returned to admin account.');
    }
}
