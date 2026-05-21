<?php

namespace App\Http\Resources\Social;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->uuid,
            'platform'         => [
                'slug'  => $this->platform?->slug,
                'name'  => $this->platform?->name,
                'icon'  => $this->platform?->icon,
                'color' => $this->platform?->color,
            ],
            'account_name'     => $this->account_name,
            'username'         => $this->username,
            'email'            => $this->email,
            'avatar'           => $this->avatar,
            'status'           => $this->status,
            'is_active'        => $this->isActive(),
            'is_expired'       => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'token_expires_at' => $this->token_expires_at?->toIso8601String(),
            'last_synced_at'   => $this->last_synced_at?->diffForHumans(),
            'pages_count'      => $this->whenCounted('pages'),
            'connected_at'     => $this->created_at->toIso8601String(),
        ];
    }
}
