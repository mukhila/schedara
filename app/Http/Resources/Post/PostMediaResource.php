<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'               => $this->uuid,
            'media_type'         => $this->media_type,
            'file_url'           => $this->publicUrl(),
            'thumbnail_url'      => $this->thumbnailUrl(),
            'mime_type'          => $this->mime_type,
            'file_size'          => $this->file_size,
            'formatted_size'     => $this->formattedSize(),
            'width'              => $this->width,
            'height'             => $this->height,
            'duration'           => $this->duration,
            'is_watermarked'     => $this->is_watermarked,
            'sort_order'         => $this->sort_order,
            'processing_status'  => $this->processing_status,
        ];
    }
}
