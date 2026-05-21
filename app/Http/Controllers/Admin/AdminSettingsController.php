<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        $grouped = SystemSetting::grouped();

        return view('admin.settings.index', compact('grouped'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['settings' => 'required|array']);

        foreach ($request->input('settings', []) as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AdminActivityLog::record('update', 'settings', 'Updated system settings');

        return back()->with('success', 'Settings saved.');
    }
}
