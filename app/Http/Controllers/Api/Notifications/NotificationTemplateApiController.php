<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Services\Notifications\NotificationTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationTemplateApiController extends Controller
{
    public function __construct(
        private readonly NotificationTemplateService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId  = app()->bound('current.tenant') ? app('current.tenant')->id : null;
        $channel   = $request->query('channel');
        $templates = $this->service->forTenant($tenantId, $channel);

        return response()->json($templates);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId  = app()->bound('current.tenant') ? app('current.tenant')->id : null;
        $validated = $request->validate([
            'template_name'    => 'required|string|max:100',
            'type'             => 'required|string|max:80',
            'channel'          => 'required|in:email,push,whatsapp,slack,sms',
            'subject'          => 'nullable|string|max:200',
            'message_template' => 'required|string',
            'variables'        => 'nullable|array',
            'status'           => 'nullable|in:active,inactive',
        ]);

        $template = $this->service->create(array_merge($validated, ['tenant_id' => $tenantId]));

        return response()->json($template, 201);
    }

    public function show(NotificationTemplate $notificationTemplate): JsonResponse
    {
        return response()->json($notificationTemplate);
    }

    public function update(Request $request, NotificationTemplate $notificationTemplate): JsonResponse
    {
        $validated = $request->validate([
            'template_name'    => 'sometimes|string|max:100',
            'subject'          => 'nullable|string|max:200',
            'message_template' => 'sometimes|string',
            'variables'        => 'nullable|array',
            'status'           => 'nullable|in:active,inactive',
        ]);

        $updated = $this->service->update($notificationTemplate, $validated);

        return response()->json($updated);
    }

    public function destroy(NotificationTemplate $notificationTemplate): JsonResponse
    {
        $this->service->delete($notificationTemplate);

        return response()->json(null, 204);
    }

    /** Preview a template with sample variable substitution. */
    public function preview(Request $request, NotificationTemplate $notificationTemplate): JsonResponse
    {
        $variables = $request->input('variables', []);
        $rendered  = $notificationTemplate->render($variables);

        return response()->json(['preview' => $rendered, 'declared_variables' => $notificationTemplate->extractVariables()]);
    }
}
