<?php

namespace App\DTOs\Social;

use Laravel\Socialite\Contracts\User as SocialiteUser;

readonly class SocialAccountDTO
{
    public function __construct(
        public string  $platformUserId,
        public string  $accountName,
        public ?string $username,
        public ?string $email,
        public ?string $avatar,
        public string  $accessToken,
        public ?string $refreshToken,
        public ?int    $expiresIn,      // seconds from now
        public array   $scopes,
        public array   $metadata,
    ) {}

    public static function fromSocialite(SocialiteUser $user, array $extraMeta = []): self
    {
        return new self(
            platformUserId: $user->getId(),
            accountName:    $user->getName() ?? $user->getNickname() ?? $user->getEmail() ?? 'Unknown',
            username:       $user->getNickname(),
            email:          $user->getEmail(),
            avatar:         $user->getAvatar(),
            accessToken:    $user->token,
            refreshToken:   $user->refreshToken ?? null,
            expiresIn:      isset($user->expiresIn) ? (int) $user->expiresIn : null,
            scopes:         isset($user->approvedScopes) ? (array) $user->approvedScopes : [],
            metadata:       array_merge($user->getRaw() ?? [], $extraMeta),
        );
    }

    public function tokenExpiresAt(): ?\Carbon\Carbon
    {
        return $this->expiresIn
            ? now()->addSeconds($this->expiresIn)
            : null;
    }

    public function toArray(): array
    {
        return [
            'platform_user_id' => $this->platformUserId,
            'account_name'     => $this->accountName,
            'username'         => $this->username,
            'email'            => $this->email,
            'avatar'           => $this->avatar,
            'access_token'     => $this->accessToken,
            'refresh_token'    => $this->refreshToken,
            'token_expires_at' => $this->tokenExpiresAt(),
            'scopes'           => $this->scopes,
            'metadata'         => $this->metadata,
            'status'           => 'active',
        ];
    }
}
