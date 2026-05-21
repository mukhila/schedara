<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HashtagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hashtag'        => $this->hashtag,
            'with_hash'      => $this->withHash(),
            'usage_count'    => $this->usage_count,
            'avg_engagement' => $this->avg_engagement,
            'is_trending'    => $this->is_trending,
            'group_name'     => $this->group_name,
        ];
    }
}
