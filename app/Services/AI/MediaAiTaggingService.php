<?php

namespace App\Services\AI;

use App\Models\MediaActivityLog;
use App\Models\MediaLibrary;
use App\Services\Media\MediaTagService;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;

class MediaAiTaggingService
{
    public function __construct(private readonly MediaTagService $tagService) {}

    public function generateTags(MediaLibrary $media): array
    {
        if (!config('openai.api_key')) {
            return [];
        }

        try {
            $url  = $media->publicUrl();
            $tags = $this->analyzeImage($url);

            if (!empty($tags)) {
                $this->tagService->syncTags($media, $media->tenant_id, $tags);
                MediaActivityLog::record($media, 'ai_tagged', 'success', ['tags' => $tags]);
            }

            return $tags;
        } catch (\Throwable $e) {
            MediaActivityLog::record($media, 'ai_tagging_failed', 'error', [], $e->getMessage());
            return [];
        }
    }

    public function generateAltText(MediaLibrary $media): string
    {
        if (!config('openai.api_key') || !$media->isImage()) return '';

        try {
            $response = OpenAI::chat()->create([
                'model'    => 'gpt-4o-mini',
                'messages' => [[
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'text',      'text' => 'Generate a concise, descriptive alt text (max 120 chars) for this image. Return only the alt text.'],
                        ['type' => 'image_url', 'image_url' => ['url' => $media->publicUrl()]],
                    ],
                ]],
                'max_tokens' => 150,
            ]);

            $altText = trim($response->choices[0]->message->content);
            $media->update(['alt_text' => $altText]);

            return $altText;
        } catch (\Throwable) {
            return '';
        }
    }

    private function analyzeImage(string $url): array
    {
        $response = OpenAI::chat()->create([
            'model'    => 'gpt-4o-mini',
            'messages' => [[
                'role'    => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Analyze this image and return 5-10 relevant tags for content categorization. Return ONLY a comma-separated list of single words or short phrases, no explanations.'],
                    ['type' => 'image_url', 'image_url' => ['url' => $url]],
                ],
            ]],
            'max_tokens' => 100,
        ]);

        $raw  = trim($response->choices[0]->message->content);
        $tags = array_filter(array_map('trim', explode(',', $raw)));

        return array_values(array_map('strtolower', $tags));
    }
}
