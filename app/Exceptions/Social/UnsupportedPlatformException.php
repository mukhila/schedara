<?php

namespace App\Exceptions\Social;

use RuntimeException;

class UnsupportedPlatformException extends RuntimeException
{
    public function __construct(string $platform)
    {
        parent::__construct("Social platform '{$platform}' is not supported.");
    }
}
