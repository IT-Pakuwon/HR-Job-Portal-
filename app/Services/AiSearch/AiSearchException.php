<?php

namespace App\Services\AiSearch;

use RuntimeException;

class AiSearchException extends RuntimeException
{
    public function __construct(string $message, public readonly int $httpStatus = 422)
    {
        parent::__construct($message);
    }
}
