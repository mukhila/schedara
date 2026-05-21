<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'             => $this->uuid,
            'title'            => $this->title,
            'content'          => $this->content,
            'caption'          => $this->caption,
            'type'             => $this->type,
            'status'           => $this->status,
            'status_color'     => $this->statusColor(),
            'platforms'        => $this->platforms,
            'scheduled_at'     => $this->scheduled_at?->toIso8601String(),
            'timezone'         => $this->timezone,
            'published_at'     => $this->published_at?->toIso8601String(),
            'is_evergreen'     => $this->is_evergreen,
            'auto_repost'      => $this->auto_repost,
            'repost_frequency' => $this->repost_frequency,
            'next_repost_at'   => $this->next_repost_at?->toIso8601String(),
            'hashtags'         => $this->whenLoaded('hashtags', fn () => $this->hashtags->pluck('hashtag')),
            'media'            => PostMediaResource::collection($this->whenLoaded('media')),
            'platform_configs' => $this->whenLoaded('platformConfigs', fn () =>
                $this->platformConfigs->map(fn ($c) => [
                    'platform'        => $c->platform,
                    'status'          => $c->status,
                    'content_override'=> $c->content_override,
                    'first_comment'   => $c->first_comment,
                    'platform_post_id'=> $c->platform_post_id,
                    'published_at'    => $c->published_at?->toIso8601String(),
                ])
            ),
            'created_at'       => $this->created_at->toIso8601String(),
            'updated_at'       => $this->updated_at->toIso8601String(),
        ];
    }
}
