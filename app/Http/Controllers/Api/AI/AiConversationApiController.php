<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Services\AI\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiConversationApiController extends Controller
{
    public function __construct(private readonly ConversationService $conversations) {}

    // GET /api/ai/assistant/conversations
    public function index(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $list   = $this->conversations->forUser($request->user()->id, $tenant->id);

        return response()->json(['data' => $list->map(fn ($c) => [
            'uuid'            => $c->uuid,
            'title'           => $c->autoTitle(),
            'ai_provider'     => $c->ai_provider,
            'ai_model'        => $c->ai_model,
            'message_count'   => $c->message_count,
            'last_message_at' => $c->last_message_at?->toIso8601String(),
        ])]);
    }

    // POST /api/ai/assistant/conversations
    public function store(Request $request): JsonResponse
    {
        $data   = $request->validate([
            'provider' => 'nullable|in:openai,claude,gemini',
            'model'    => 'nullable|string',
        ]);
        $tenant = app('current.tenant');
        $convo  = $this->conversations->create($tenant->id, $request->user()->id, $data['provider'] ?? 'openai', $data['model'] ?? null);

        return response()->json(['data' => ['uuid' => $convo->uuid]], 201);
    }

    // GET /api/ai/assistant/conversations/{uuid}
    public function show(string $uuid, Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $convo  = AiConversation::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();

        return response()->json(['data' => [
            'uuid'       => $convo->uuid,
            'title'      => $convo->autoTitle(),
            'provider'   => $convo->ai_provider,
            'model'      => $convo->ai_model,
            'messages'   => $convo->messages->map(fn ($m) => [
                'uuid'       => $m->uuid,
                'role'       => $m->role,
                'content'    => $m->content,
                'created_at' => $m->created_at->toIso8601String(),
            ]),
        ]]);
    }

    // POST /api/ai/assistant/conversations/{uuid}/messages
    public function message(string $uuid, Request $request): JsonResponse
    {
        $data = $request->validate([
            'message'        => 'required|string|max:10000',
            'brand_voice_id' => 'nullable|integer',
        ]);

        $tenant = app('current.tenant');
        $convo  = AiConversation::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();

        try {
            $reply = $this->conversations->sendMessage(
                $convo,
                $data['message'],
                $tenant->id,
                $request->user()->id,
                $data['brand_voice_id'] ?? null,
            );

            return response()->json(['data' => [
                'uuid'       => $reply->uuid,
                'role'       => 'assistant',
                'content'    => $reply->content,
                'created_at' => $reply->created_at->toIso8601String(),
            ]]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // DELETE /api/ai/assistant/conversations/{uuid}
    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        $convo  = AiConversation::where('uuid', $uuid)->where('tenant_id', $tenant->id)->firstOrFail();
        $this->conversations->delete($convo);

        return response()->json(['message' => 'Conversation deleted.']);
    }
}
