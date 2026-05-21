<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $service) {}

    public function index(Request $request): View
    {
        $user      = $request->user();
        $category  = $request->input('category');
        $filter    = $request->input('filter');

        $notifications = $this->service->forUser(
            userId:   $user->id,
            perPage:  30,
            category: $category,
            filter:   $filter,
        );

        $unreadCount = $this->service->unreadCount($user->id);
        $categories  = config('notifications.categories', []);

        return view('backend.notifications.index', compact(
            'notifications', 'unreadCount', 'categories', 'category', 'filter'
        ));
    }

    public function preferences(Request $request): View
    {
        $user        = $request->user();
        $preferences = $this->service->getPreferences($user->id);
        $categories  = config('notifications.categories', []);
        $channels    = config('notifications.channels', []);

        return view('backend.notifications.preferences', compact('preferences', 'categories', 'channels'));
    }

    public function templates(): View
    {
        return view('backend.notifications.templates');
    }
}
