<?php

namespace App\Events\Media;

use App\Models\MediaLibrary;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly MediaLibrary $media) {}
}
