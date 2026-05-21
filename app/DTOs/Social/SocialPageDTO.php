<?php

namespace App\DTOs\Social;

readonly class SocialPageDTO
{
    public function __construct(
        public string  $pageId,
        public string  $pageName,
        public string  $pageType,
        public ?string $category,
        public ?string $avatar,
        public ?string $accessToken,
        public int     $followersCount,
        public array   $metadata,
    ) {}

    public function toArray(): array
    {
        return [
            'page_id'         => $this->pageId,
            'page_name'       => $this->pageName,
            'page_type'       => $this->pageType,
            'category'        => $this->category,
            'avatar'          => $this->avatar,
            'access_token'    => $this->accessToken,
            'followers_count' => $this->followersCount,
            'metadata'        => $this->metadata,
        ];
    }
}
