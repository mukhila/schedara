<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'                => $this->uuid,
            'name'                => $this->name,
            'original_name'       => $this->original_name,
            'type'                => $this->type,
            'mime_type'           => $this->mime_type,
            'extension'           => $this->extension,
            'size'                => $this->size,
            'human_size'          => $this->humanSize(),
            'url'                 => $this->publicUrl(),
            'thumbnail_url'       => $this->thumbnailPublicUrl(),
            'preview_url'         => $this->previewUrl(),
            'width'               => $this->width,
            'height'              => $this->height,
            'duration'            => $this->duration,
            'human_duration'      => $this->humanDuration(),
            'alt_text'            => $this->alt_text,
            'is_favorite'         => $this->is_favorite,
            'share_url'           => $this->shareUrl(),
            'optimization_status' => $this->optimization_status,
            'compression_status'  => $this->compression_status,
            'approval_status'     => $this->approval_status,
            'version'             => $this->version,
            'folder'              => $this->whenLoaded('folder', fn () => [
                'uuid' => $this->folder?->uuid,
                'name' => $this->folder?->name,
            ]),
            'tags'                => $this->whenLoaded('mediaTags', fn () =>
                $this->mediaTags->map(fn ($t) => ['slug' => $t->slug, 'name' => $t->tag_name, 'color' => $t->color])
            ),
            'approval'            => $this->whenLoaded('approval', fn () => $this->approval ? [
                'status'      => $this->approval->status,
                'comments'    => $this->approval->comments,
                'approved_at' => $this->approval->approved_at?->toIso8601String(),
            ] : null),
            'created_at'          => $this->created_at->toIso8601String(),
            'updated_at'          => $this->updated_at->toIso8601String(),
        ];
    }
}
