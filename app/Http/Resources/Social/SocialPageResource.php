<?php

namespace App\Http\Resources\Social;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->uuid,
            'page_id'         => $this->page_id,
            'page_name'       => $this->page_name,
            'page_type'       => $this->page_type,
            'category'        => $this->category,
            'avatar'          => $this->avatar,
            'followers_count' => $this->followers_count,
            'is_selected'     => $this->is_selected,
            'updated_at'      => $this->updated_at->toIso8601String(),
        ];
    }
}
