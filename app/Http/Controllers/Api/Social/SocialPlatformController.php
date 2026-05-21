<?php

namespace App\Http\Controllers\API\Social;

use App\Http\Controllers\Controller;
use App\Http\Resources\Social\SocialPlatformResource;
use App\Models\SocialPlatform;

class SocialPlatformController extends Controller
{
    public function index()
    {
        $platforms = SocialPlatform::active()->get();

        return SocialPlatformResource::collection($platforms);
    }
}
