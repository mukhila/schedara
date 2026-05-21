<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\SlackIntegration;
use App\Services\Notifications\Channels\SlackChannelProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlackIntegrationController extends Controller
{
    public function index(): View
    {
        $tenant      = app('current.tenant');
        $integration = SlackIntegration::where('tenant_id', $tenant->id)->withTrashed()->first();

        return view('backend.notifications.slack', compact('tenant', 'integration'));
    }

    public function connect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'webhook_url'      => 'required|url|max:512',
            'channel_name'     => 'required|string|max:100',
            'workspace_name'   => 'nullable|string|max:100',
        ]);

        $tenant = app('current.tenant');

        SlackIntegration::updateOrCreate(
            ['tenant_id' => $tenant->id],
            array_merge($validated, ['status' => 'active', 'deleted_at' => null])
        );

        return back()->with('success', 'Slack integration connected successfully.');
    }

    public function test(Request $request): RedirectResponse
    {
        $tenant      = app('current.tenant');
        $integration = SlackIntegration::where('tenant_id', $tenant->id)->where('status', 'active')->first();

        if (! $integration) {
            return back()->with('error', 'No active Slack integration found.');
        }

        try {
            app(SlackChannelProvider::class)->sendToWebhook(
                $integration,
                'Schedara Test Notification',
                'Your Slack integration is working correctly.',
                config('app.url')
            );

            return back()->with('success', 'Test message sent to Slack!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Slack test failed: ' . $e->getMessage());
        }
    }

    public function disconnect(): RedirectResponse
    {
        $tenant = app('current.tenant');

        SlackIntegration::where('tenant_id', $tenant->id)->delete();

        return back()->with('success', 'Slack integration disconnected.');
    }
}
