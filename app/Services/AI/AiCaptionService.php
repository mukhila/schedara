<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;

class AiCaptionService
{
    public function generateCaption(string $content, string $platform, string $tone = 'professional'): string
    {
        $platformNotes = $this->platformNotes($platform);

        $prompt = <<<PROMPT
You are a social media expert. Generate a compelling {$tone} caption for {$platform}.

Platform requirements: {$platformNotes}

Post content/topic:
{$content}

Return ONLY the caption text. No quotes, no explanation.
PROMPT;

        $response = OpenAI::chat()->create([
            'model'    => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert social media copywriter.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'max_tokens'  => 500,
            'temperature' => 0.7,
        ]);

        return trim($response->choices[0]->message->content);
    }

    public function generateHashtags(string $content, string $platform, int $count = 10): array
    {
        $prompt = <<<PROMPT
Generate {$count} relevant hashtags for this {$platform} post. Return ONLY hashtags, one per line, without the # symbol.

Post content:
{$content}
PROMPT;

        $response = OpenAI::chat()->create([
            'model'    => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens'  => 200,
            'temperature' => 0.5,
        ]);

        $raw  = trim($response->choices[0]->message->content);
        $tags = array_filter(array_map('trim', explode("\n", $raw)));

        return array_values(array_map(fn ($t) => ltrim($t, '#'), $tags));
    }

    public function suggestBestTime(string $platform, string $industry = 'general'): array
    {
        $defaults = [
            'facebook'  => ['09:00', '13:00', '17:00'],
            'instagram' => ['08:00', '12:00', '19:00'],
            'twitter'   => ['08:00', '12:00', '17:00', '21:00'],
            'linkedin'  => ['08:00', '12:00', '17:00'],
            'pinterest' => ['20:00', '21:00'],
            'youtube'   => ['15:00', '18:00'],
            'threads'   => ['08:00', '18:00'],
        ];

        return [
            'times'    => $defaults[$platform] ?? ['09:00', '17:00'],
            'days'     => ['Tuesday', 'Wednesday', 'Thursday'],
            'timezone' => 'UTC',
        ];
    }

    private function platformNotes(string $platform): string
    {
        return match ($platform) {
            'instagram' => 'Max 2200 characters, emoji-friendly, include call-to-action, hashtags at end',
            'twitter'   => 'Max 280 characters, concise, punchy, optional hashtags',
            'linkedin'  => 'Professional tone, max 3000 characters, industry insights welcome',
            'facebook'  => 'Conversational, max 63206 characters, questions boost engagement',
            'pinterest' => 'Descriptive, keyword-rich, max 500 characters',
            'youtube'   => 'First 100 characters most visible, include keywords',
            'threads'   => 'Conversational, max 500 characters, casual tone',
            default     => 'Clear, engaging, platform-appropriate',
        };
    }
}
