<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdminActivityLog::with('admin');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('admin')) {
            $query->where('admin_id', $request->admin);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs    = $query->latest('created_at')->paginate(50)->withQueryString();
        $modules = AdminActivityLog::distinct()->orderBy('module')->pluck('module');
        $admins  = User::where('is_super_admin', true)->get(['id', 'name']);

        return view('admin.activity.index', compact('logs', 'modules', 'admins'));
    }
}
