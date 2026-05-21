<?php

namespace App\Http\Resources\Social;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialPlatformResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'icon'         => $this->icon,
            'color'        => $this->color,
            'capabilities' => $this->capabilities,
            'connect_url'  => route('social.connect', $this->slug),
        ];
    }
}
