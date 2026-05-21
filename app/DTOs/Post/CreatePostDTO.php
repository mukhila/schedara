<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

readonly class CreatePostDTO
{
    public function __construct(
        public string  $content,
        public ?string $caption,
        public string  $type,
        public string  $status,
        public array   $platforms,           // ['facebook', 'instagram', ...]
        public array   $platformAccounts,    // ['facebook' => account_uuid, ...]
        public ?string $scheduledAt,
        public string  $timezone,
        public bool    $isEvergreen,
        public bool    $autoRepost,
        public ?int    $repostFrequency,
        public array   $hashtags,            // ['marketing', 'growth', ...]
        public ?string $title,
        public array   $platformOverrides,   // per-platform caption/first_comment
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            content:           $request->input('content', ''),
            caption:           $request->input('caption'),
            type:              $request->input('type', 'text'),
            status:            $request->input('status', 'draft'),
            platforms:         $request->input('platforms', []),
            platformAccounts:  $request->input('platform_accounts', []),
            scheduledAt:       $request->input('scheduled_at'),
            timezone:          $request->input('timezone', 'UTC'),
            isEvergreen:       (bool) $request->input('is_evergreen', false),
            autoRepost:        (bool) $request->input('auto_repost', false),
            repostFrequency:   $request->input('repost_frequency') ? (int) $request->input('repost_frequency') : null,
            hashtags:          $request->input('hashtags', []),
            title:             $request->input('title'),
            platformOverrides: $request->input('platform_overrides', []),
        );
    }
}
