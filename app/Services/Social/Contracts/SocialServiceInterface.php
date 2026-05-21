<?php

namespace App\Services\Social\Contracts;

use App\DTOs\Social\SocialPageDTO;
use App\Models\Post;
use App\Models\PostPlatformConfig;
use App\Models\SocialAccount;

interface SocialServiceInterface
{
    /** Return platform slug handled by this service */
    public function platform(): string;

    /** Fetch fresh profile data from the platform API */
    public function getProfile(SocialAccount $account): array;

    /** Fetch pages / channels / boards linked to this account */
    public function getPages(SocialAccount $account): array; // SocialPageDTO[]

    /**
     * Publish a post to this platform.
     * Returns the platform-assigned post ID on success.
     * Throws on failure.
     */
    public function publishPost(Post $post, PostPlatformConfig $config): string;

    /** Exchange / rotate an expired token; returns updated account */
    public function refreshToken(SocialAccount $account): SocialAccount;

    /** Revoke the token on the platform side */
    public function revokeToken(SocialAccount $account): bool;
}
