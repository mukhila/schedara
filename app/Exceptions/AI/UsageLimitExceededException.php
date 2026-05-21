<?php

namespace App\Exceptions\AI;

use RuntimeException;

class UsageLimitExceededException extends RuntimeException
{
    public function __construct(string $message = 'Monthly AI token limit reached.')
    {
        parent::__construct($message);
    }
}
