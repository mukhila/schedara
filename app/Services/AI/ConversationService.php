<?php

namespace App\Services\AI;

use App\DTOs\AI\AiRequestDTO;
use App\Models\AiConversation;
use App\Models\AiConversationMessage;
use App\Models\AiBrandVoice;

class ConversationService
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator,
        private readonly PromptBuilder  $prompts,
    ) {}

    public function create(int $tenantId, int $userId, string $provider = 'openai', ?string $model = null): AiConversation
    {
        return AiConversation::create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'ai_provider' => $provider,
            'ai_model'    => $model ?? config("ai.providers.{$provider}.default_model"),
        ]);
    }

    public function sendMessage(
        AiConversation $conversation,
        string         $userMessage,
        int            $tenantId,
        int            $userId,
        ?int           $brandVoiceId = null,
    ): AiConversationMessage {
        // Save the user message
        AiConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'user',
            'content'         => $userMessage,
        ]);

        $brandVoice = $brandVoiceId
            ? AiBrandVoice::find($brandVoiceId)
            : null;

        // Build messages array from history (last 20 to stay within context)
        $history = $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->latest()
            ->limit(20)
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();

        // Remove the last message (the one we just saved — it'll be the prompt)
        $history = array_slice($history, 0, -1);

        $request = new AiRequestDTO(
            prompt:       $userMessage,
            model:        $conversation->ai_model ?? '',
            systemPrompt: $this->prompts->chatSystem($brandVoice),
            temperature:  0.7,
            maxTokens:    2000,
            history:      $history,
        );

        $response = $this->orchestrator->generate(
            $request, 'chat', $tenantId, $userId,
            $conversation->ai_provider,
            $conversation->ai_model,
        );

        // Save the assistant reply
        $assistantMessage = AiConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'assistant',
            'content'         => $response->content,
            'tokens_used'     => $response->totalTokens,
        ]);

        // Auto-generate conversation title from first exchange
        if ($conversation->message_count <= 2 && !$conversation->title) {
            $conversation->update(['title' => $conversation->autoTitle()]);
        }

        return $assistantMessage;
    }

    public function forUser(int $userId, int $tenantId)
    {
        return AiConversation::forUser($userId, $tenantId)
            ->orderByDesc('last_message_at')
            ->get();
    }

    public function delete(AiConversation $conversation): void
    {
        $conversation->delete();
    }
}
