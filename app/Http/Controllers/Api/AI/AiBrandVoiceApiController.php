<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Models\AiBrandVoice;
use App\Services\AI\BrandVoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiBrandVoiceApiController extends Controller
{
    public function __construct(private readonly BrandVoiceService $service) {}

    // GET /api/ai/assistant/brand-voices
    public function index(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $voices = $this->service->forTenant($tenant->id);

        return response()->json(['data' => $voices->map(fn ($v) => [
            'uuid'            => $v->uuid,
            'name'            => $v->name,
            'description'     => $v->description,
            'industry'        => $v->industry,
            'tone_attributes' => $v->tone_attributes,
            'brand_keywords'  => $v->brand_keywords,
            'is_default'      => $v->is_default,
        ])]);
    }

    // POST /api/ai/assistant/brand-voices
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:128',
            'description'         => 'nullable|string|max:500',
            'industry'            => 'nullable|string|max:128',
            'tone_attributes'     => 'required|array|min:1|max:10',
            'tone_attributes.*'   => 'string|max:64',
            'brand_keywords'      => 'nullable|array',
            'brand_keywords.*'    => 'string|max:64',
            'example_content'     => 'nullable|string|max:5000',
            'custom_instructions' => 'nullable|string|max:2000',
            'is_default'          => 'boolean',
        ]);

        $tenant = app('current.tenant');
        $voice  = $this->service->create($tenant->id, $data);

        return response()->json(['data' => ['uuid' => $voice->uuid, 'name' => $voice->name]], 201);
    }

    // PUT /api/ai/assistant/brand-voices/{uuid}
    public function update(string $uuid, Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $voice  = AiBrandVoice::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();

        $data = $request->validate([
            'name'                => 'sometimes|string|max:128',
            'description'         => 'nullable|string|max:500',
            'industry'            => 'nullable|string|max:128',
            'tone_attributes'     => 'sometimes|array|min:1',
            'brand_keywords'      => 'nullable|array',
            'example_content'     => 'nullable|string|max:5000',
            'custom_instructions' => 'nullable|string|max:2000',
            'is_default'          => 'boolean',
        ]);

        $updated = $this->service->update($voice, $data);

        return response()->json(['data' => ['uuid' => $updated->uuid]]);
    }

    // DELETE /api/ai/assistant/brand-voices/{uuid}
    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        AiBrandVoice::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail()->delete();

        return response()->json(['message' => 'Brand voice deleted.']);
    }

    // POST /api/ai/assistant/brand-voices/analyze
    public function analyze(Request $request): JsonResponse
    {
        $data = $request->validate([
            'examples' => 'required|string|min:100',
            'industry' => 'nullable|string|max:128',
        ]);

        $tenant = app('current.tenant');

        try {
            $analysis = $this->service->analyze($data, $tenant->id, $request->user()->id);
            return response()->json(['data' => $analysis]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
