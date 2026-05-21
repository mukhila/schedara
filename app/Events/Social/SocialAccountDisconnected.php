<?php

namespace App\Events\Social;

use App\Models\SocialAccount;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SocialAccountDisconnected
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly SocialAccount $account) {}
}
