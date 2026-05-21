<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Models\AiTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiTemplateApiController extends Controller
{
    // GET /api/ai/assistant/templates
    public function index(Request $request): JsonResponse
    {
        $tenant    = app('current.tenant');
        $templates = AiTemplate::forTenant($tenant->id)
            ->when($request->type, fn ($q, $t) => $q->ofType($t))
            ->orderByDesc('usage_count')
            ->get();

        return response()->json(['data' => $templates->map(fn ($t) => [
            'uuid'          => $t->uuid,
            'name'          => $t->template_name,
            'type'          => $t->template_type,
            'description'   => $t->description,
            'variables'     => $t->variables ?? [],
            'provider'      => $t->ai_provider,
            'model'         => $t->ai_model,
            'is_public'     => $t->is_public,
            'is_system'     => $t->is_system,
            'usage_count'   => $t->usage_count,
        ])]);
    }

    // POST /api/ai/assistant/templates
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'template_name'    => 'required|string|max:128',
            'template_type'    => 'required|in:caption,hashtag,content_ideas,seo,ad_copy,response,campaign,custom',
            'description'      => 'nullable|string|max:500',
            'prompt_template'  => 'required|string',
            'variables'        => 'nullable|array',
            'variables.*'      => 'string',
            'ai_provider'      => 'nullable|in:openai,claude,gemini',
            'ai_model'         => 'nullable|string',
            'is_public'        => 'boolean',
        ]);

        $tenant   = app('current.tenant');
        $template = AiTemplate::create(array_merge($data, [
            'tenant_id'  => $tenant->id,
            'created_by' => $request->user()->id,
        ]));

        return response()->json(['data' => ['uuid' => $template->uuid, 'name' => $template->template_name]], 201);
    }

    // PUT /api/ai/assistant/templates/{uuid}
    public function update(string $uuid, Request $request): JsonResponse
    {
        $tenant   = app('current.tenant');
        $template = AiTemplate::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();

        $data = $request->validate([
            'template_name'   => 'sometimes|string|max:128',
            'description'     => 'nullable|string|max:500',
            'prompt_template' => 'sometimes|string',
            'variables'       => 'nullable|array',
            'is_public'       => 'boolean',
        ]);

        $template->update($data);

        return response()->json(['data' => ['uuid' => $template->uuid]]);
    }

    // DELETE /api/ai/assistant/templates/{uuid}
    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $tenant   = app('current.tenant');
        $template = AiTemplate::where('uuid', $uuid)
            ->where('tenant_id', $tenant->id)
            ->where('is_system', false)
            ->firstOrFail();

        $template->delete();

        return response()->json(['message' => 'Template deleted.']);
    }
}
